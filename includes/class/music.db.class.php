<?php
require_once "db.class.php";

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
        return $this->getDb()->query("SELECT m.uid, m.title, m.notes, m.music_link, m.genre 
                                      FROM kcb_music m 
                                      WHERE m.actv_flg = 1 
                                      ORDER BY m.title");
    }

    public function getMusicRecord($uid)
    {
        $this->getDb()->bind('uid', $uid);
        return $this->getDb()->row("SELECT m.uid, m.title, m.notes, m.music_link, m.genre 
                                    FROM kcb_music m 
                                    WHERE m.actv_flg = 1 
                                      AND m.uid = :uid");
    }

    public function getGenres()
    {
        return $this->getDb()->query("SELECT genre 
                                      FROM lkp_music_genre 
                                      ORDER BY sort_order");
    }

    public function checkDupMusic($title)
    {
        $this->getDb()->bind("title", $title);

        return (int)$this->getDb()->single("SELECT COUNT(*)
                                           FROM kcb_music
                                           WHERE title = :title");
    }

    public function searchTitles($title)
    {
        $this->getDb()->bind("title", '%'. $title . '%');
        return $this->getDb()->query("SELECT uid AS value, title AS label 
                                      FROM kcb_music 
                                      WHERE title LIKE :title 
                                        AND actv_flg = 1 
                                      ORDER by title");
    }

    /* UPDATE QUERIES */
    public function addMusic($title, $notes, $link, $genre, $user_id)
    {
        try {
            $this->beginTransaction();

            $this->getDb()->bind('title', $title);
            $this->getDb()->bind('notes', $notes);
            $this->getDb()->bind('link', $link);
            $this->getDb()->bind('genre', $genre);
            $this->getDb()->bind('user_id', $user_id);
            $this->getDb()->bind('user_id2', $user_id);

            $this->getDb()->query("INSERT INTO kcb_music (title, notes, music_link, genre, estbd_by, estbd_dt_tm, lst_updtd_by, lst_tran_dt_tm) 
                                   VALUES(:title, :notes, :link, :genre, :user_id, NOW(), :user_id2, NOW())");

            $uid = $this->getDb()->lastInsertId();

            if ($uid > 0) {
                $retValue = 1;
                $this->executeTransaction();
            } else {
                $this->rollBackTransaction();
                $retValue = "add_music_error";
            }
        } catch (Exception $e) {
            $this->rollBackTransaction();
            $retValue = "db_error";
        }

        return $retValue;
    }

    public function editMusic($uid, $title, $notes, $link, $genre, $user_id)
    {
        try {
            $this->beginTransaction();

            $this->getDb()->bind('uid', $uid);
            $this->getDb()->bind('title', $title);
            $this->getDb()->bind('notes', $notes);
            $this->getDb()->bind('link', $link);
            $this->getDb()->bind('genre', $genre);
            $this->getDb()->bind('user_id', $user_id);

            $retValue = $this->getDb()->query("UPDATE kcb_music 
                                               SET title = :title, notes = :notes, music_link = :link, genre = :genre, lst_updtd_by = :user_id, lst_tran_dt_tm  = NOW() 
                                               WHERE UID = :uid");

            if ($retValue) {
                $this->executeTransaction();
            } else {
                $this->rollBackTransaction();
                $retValue = "edit_music_error";
            }
        } catch (Exception $e) {
            $this->rollBackTransaction();
            $retValue = "db_error";
        }

        return $retValue;
    }

    public function deleteMusic($uid, $user_id)
    {
        $this->getDb()->bind('uid', $uid);
        $this->getDb()->bind('user_id', $user_id);

        return $this->getDb()->query("UPDATE kcb_music 
                                      SET actv_flg = 0, lst_tran_dt_tm=NOW(), lst_updtd_by=:user_id 
                                      WHERE uid = :uid");
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
