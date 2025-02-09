<?php
    include_once("db.class.php");

class MusicDB
{
    private $db;

    /* PUBLIC FUNCTIONS */
    public function __construct()
    {
        $this->setDB(new Db());
    }

    /* Transactions */
    public function beginTransaction()
    {
        $this->getDb()->beginTransaction();
    }

    public function executeTransaction()
    {
        $this->getDb()->executeTransaction();
    }

    public function rollBackTransaction()
    {
        $this->getDb()->rollBack();
    }

    /* SELECT ONLY QUERIES */

    // Gets the members by instrument
    public function getMusic()
    {
        return $this->getDb()->query("SELECT m.uid, m.title, m.notes, m.music_link, m.genre, (SELECT DATE(last_played) FROM kcb_music_last_played where music_uid = m.UID ORDER BY last_played DESC LIMIT 1) as last_played, ( SELECT COUNT(*) FROM kcb_music_last_played WHERE music_uid = m.UID ) AS number_plays FROM kcb_music m WHERE m.actv_flg = 1 ORDER BY m.title");
    }

    public function getMusicRecord($uid)
    {
        $this->getDb()->bind('uid', $uid);
        return $this->getDb()->row("SELECT m.uid, m.title, m.notes, m.music_link, m.genre, (SELECT DATE(last_played) FROM kcb_music_last_played where music_uid = m.UID ORDER BY last_played DESC LIMIT 1) as last_played, ( SELECT COUNT(*) FROM kcb_music_last_played WHERE music_uid = m.UID ) AS number_plays FROM kcb_music m WHERE m.actv_flg = 1 AND m.uid = :uid");
    }

    public function getLastPlayedDatesByDate($uid, $date)
    {
        $this->getDb()->bind('uid', $uid);
        $this->getDb()->bind('date', date("Y-m-d H:i:s", strtotime($date)));

        return $this->getDb()->resultCount("SELECT last_played from kcb_music_last_played WHERE music_uid = :uid AND last_played = :date");
    }

    public function getGenres()
    {
        return $this->getDb()->query("SELECT genre from lkp_music_genre ORDER by sort_order");
    }

    public function checkDupMusic($title)
    {
        $this->getDb()->bind("title", $title);

        return $this->getDb()->resultCount("SELECT title FROM kcb_music where title=:title");
    }

    public function searchTitles($title)
    {
        $this->getDb()->bind("title", '%'. $title . '%');
        return $this->getDb()->query("SELECT uid as value, title as label FROM kcb_music where title LIKE :title AND actv_flg = 1 ORDER by title");
    }

    /* UPDATE QUERIES */
    public function addConcert($concert_uid, $concert_dt, $user_id)
    {
        $this->getDb()->bind('music_uid', $concert_uid);
        $this->getDb()->bind('last_played', date("Y-m-d H:i:s", strtotime($concert_dt)));
        $this->getDb()->bind('user_id', $user_id);
        $this->getDb()->bind('user_id2', $user_id);

        return $this->getDb()->query("INSERT INTO kcb_music_last_played (music_uid, last_played, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) VALUES(:music_uid, :last_played, :user_id, now(), :user_id2, now())");
    }

    public function addMusic($title, $notes, $link, $genre, $last_played, $user_id)
    {
        $retValue = "add_music_error";

        try {
            $this->beginTransaction();

            $this->getDb()->bind('title', $title);
            $this->getDb()->bind('notes', $notes);
            $this->getDb()->bind('link', $link);
            $this->getDb()->bind('genre', $genre);
            $this->getDb()->bind('user_id', $user_id);
            $this->getDb()->bind('user_id2', $user_id);

            $this->getDb()->query("INSERT INTO kcb_music (title, notes, music_link, genre, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) VALUES(:title, :notes, :link, :genre, :user_id, now(), :user_id2, now())");

            $uid = $this->getDb()->lastInsertId();

            if ($uid > 0) {
                if ($last_played !== "") {
                    $this->getDb()->bind('uid', $uid);
                    $this->getDb()->bind('last_played', date("Y-m-d H:i:s", strtotime($last_played)));
                    $this->getDb()->bind('user_id3', $user_id);
                    $this->getDb()->bind('user_id4', $user_id);

                    $retValue = $this->getDb()->query("INSERT INTO kcb_music_last_played (music_uid, last_played, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) VALUES(:uid, :last_played, :user_id3, now(), :user_id4, now())");

                    if ($retValue) {
                        $this->executeTransaction();
                    } else {
                        $this->rollBackTransaction();
                        $retValue = "insert_music_last_played_error";
                    }
                } else {
                    // No last played entered, skip adding to table
                    $retValue = 1;
                    $this->executeTransaction();
                }
            } else {
                $this->rollBackTransaction();
                $retValue = "insert_music_error";
            }
        } catch (Exception $e) {
            $this->getDb()->ExceptionLog($e->getMessage());
            $this->rollBackTransaction();
            $retValue = "db_error";
        }

        return $retValue;
    }

    public function editMusic($uid, $title, $notes, $link, $genre, $last_played, $user_id)
    {
        $retValue = "edit_music_error";

        try {
            $this->beginTransaction();

            $this->getDb()->bind('uid', $uid);
            $this->getDb()->bind('title', $title);
            $this->getDb()->bind('notes', $notes);
            $this->getDb()->bind('link', $link);
            $this->getDb()->bind('genre', $genre);
            $this->getDb()->bind('user_id', $user_id);

            $this->getDb()->query("UPDATE kcb_music SET title = :title, notes = :notes, music_link = :link, genre = :genre, lst_updtd_by = :user_id, lst_tran_dt_tm  = now() WHERE UID = :uid");

            if ($last_played !== "") {
                $last_played_date = date("Y-m-d H:i:s", strtotime($last_played));

                // Only add the date if it isn't already there.
                if ($this->getLastPlayedDatesByDate($uid, $last_played) === 0) {
                    $this->getDb()->bind('uid', $uid);
                    $this->getDb()->bind('last_played', $last_played_date);
                    $this->getDb()->bind('user_id3', $user_id);
                    $this->getDb()->bind('user_id4', $user_id);

                    $retValue = $this->getDb()->query("INSERT INTO kcb_music_last_played (music_uid, last_played, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) VALUES(:uid, :last_played, :user_id3, now(), :user_id4, now())");

                    if ($retValue) {
                        $this->executeTransaction();
                    } else {
                        $this->rollBackTransaction();
                        $retValue = "insert_music_last_played_error";
                    }
                } else {
                    // No last played changed, skip updating table
                    $retValue = 1;
                    $this->executeTransaction();
                }
            } else {
                // No last played entered, skip adding to table
                $retValue = 1;
                $this->executeTransaction();
            }
        } catch (Exception $e) {
            $this->getDb()->ExceptionLog($e->getMessage());
            $this->rollBackTransaction();
            $retValue = "db_error";
        }

        return $retValue;
    }

    public function deleteMusic($uid, $user_id)
    {
        $this->getDb()->bind('uid', $uid);
        $this->getDb()->bind('user_id', $user_id);

        return $this->getDb()->query("UPDATE kcb_music SET actv_flg = 0, lst_tran_dt_tm=now(), lst_updtd_by=:user_id WHERE uid = :uid");
    }

    /* PRIVATE FUNCTIONS */
    private function getDb()
    {
        return $this->db;
    }

    private function setDb($db)
    {
        $this->db = $db;
    }
}
