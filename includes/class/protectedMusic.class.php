<?php
    // This class is for methods which must be protected, so use must have a valid session to run these queries
    // member is its parent
    include_once("member.class.php");
    include_once("music.db.class.php");

class ProtectedMusic
{
    private $db;

    /* PUBLIC FUNCTIONS */
    public function __construct()
    {
        $member = new Member(true);
        $this->setDB(new MusicDB());
    }

    public function getMusic()
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['email']) && $_SESSION['email'] !== '') {
            return $this->getDb()->getMusic();
        } else {
            return "Access Denied";
        }
    }

    public function getMusicRecord($uid)
    {
        if ($this->validAdmin()) {
            return $this->getDb()->getMusicRecord($uid);
        } else {
            return "Access Denied";
        }
    }

    public function getGenres()
    {
        if ($this->validAdmin()) {
            return $this->getDb()->getGenres();
        } else {
            return "Access Denied";
        }
    }

    public function searchTitles($title)
    {
        if ($this->validAdmin()) {
            return $this->getDb()->searchTitles($title);
        } else {
            return "Access Denied";
        }
    }

    public function addConcert($concert)
    {
        if ($this->validAdmin()) {
            $concert_uids = explode(",", $concert['concert_uids']);
            if (!is_array($concert_uids)) {
                return "Invalid concert UIDs format.";
            }
            $concert_date = $concert['concert_date'];

            foreach ($concert_uids as $uid) {
                // Verify that this concert value isn't already in the database
                if ($this->getDb()->getLastPlayedDatesByDate($uid, $concert_date) === 0) {
                    // Add concert to database
                    if (!$this->getDb()->addConcert($uid, $concert_date, $_SESSION['email'])) {
                        return false;
                    }
                }
            }

            return true;
        } else {
            return "Access Denied";
        }
    }

    public function deleteMusic($uid)
    {
        if ($this->validAdmin()) {
            if ($this->getDb()->deleteMusic($uid, $_SESSION['email'])) {
                return "success";
            } else {
                return "Unable to delete. Was this item already deleted?";
            }
        } else {
            return "Access Denied";
        }
    }

    public function addMusic($title, $notes, $link, $genre, $last_played)
    {
        if ($this->validAdmin()) {
            // TODO: need to handle reactivating deleted titles
            if ($this->getDb()->checkDupMusic($title) > 0) {
                return "This title already exists.";
            } else {
                $retValue = $this->getDb()->addMusic($title, $notes, $link, $genre, $last_played, $_SESSION['email']);
                if ($retValue === 1) {
                    return "success";
                } else {
                    if ($retValue == "db_error") {
                        return "Database error. Please try again later.";
                    } elseif ($retValue == "add_music_error") {
                        return "Error adding music. Please try again later";
                    } elseif ($retValue == "insert_music_error") {
                        return "Error inserting values into the music table. Please try again later.";
                    } elseif ($retValue == "insert_music_last_played_error") {
                        return "Error inserting values into the music last played table. Please try again later.";
                    } else {
                        return "Unknown error.";
                    }
                }
            }
        } else {
            return "Access Denied";
        }
    }

    public function editMusic($uid, $title, $notes, $link, $genre, $last_played)
    {
        if ($this->validAdmin()) {
            $retValue = $this->getDb()->editMusic($uid, $title, $notes, $link, $genre, $last_played, $_SESSION['email']);
            if ($retValue === 1) {
                return "success";
            } else {
                if ($retValue == "db_error") {
                    return "Database error. Please try again later.";
                } elseif ($retValue == "add_music_error") {
                    return "Error adding music. Please try again later";
                } elseif ($retValue == "insert_music_error") {
                    return "Error inserting values into the music table. Please try again later.";
                } elseif ($retValue == "insert_music_last_played_error") {
                    return "Error inserting values into the music last played table. Please try again later.";
                } else {
                    return "Unknown error.";
                }
            }
        } else {
            return "Access Denied";
        }
    }

    /* PRIVATE FUNCTIONS */
    private function validAdmin()
    {
        $validSession = false;
        if (isset($_SESSION['accountType']) && $_SESSION['accountType'] !== "") {
            if($_SESSION['accountType'] === 1 || $_SESSION['accountType'] === 2) {
                $validSession = true;
            }
        }

        return $validSession;
    }

    private function getDb()
    {
        return $this->db;
    }

    private function setDb($db)
    {
        $this->db = $db;
    }
}
