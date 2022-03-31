<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Database Session Handling
 */
class ilSessionDBHandler implements SessionHandlerInterface
{
    /**
     * Registers the session save handler
     * session.save_handler must be 'user'
     */
    public function setSaveHandler() : bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        return session_set_save_handler(
            $this,
            true // Registers session_write_close() as a register_shutdown_function() function.
        );
    }

    /**
     * Opens session, normally a db connection would be opened here, but
     * we use the standard ilias db connection, so nothing must be done here
     * @param string $path
     * @param string $name session name [PHPSESSID]
     */
    public function open($path, $name) : bool// TODO PHP8-REVIEW Type hints missing
    {
        return true;
    }

    /**
     * close session
     *
     * for a db nothing has to be done here
     */
    public function close() : bool
    {
        return true;
    }

    /**
     * Reads data of the session identified by $session_id and returns it as a
     * serialised string. If there is no session with this ID an empty string is
     * returned
     * @param string $id
     */
    public function read($id) : string// TODO PHP8-REVIEW Type hints missing
    {
        return ilSession::_getData($id);
    }

    /**
     * Writes serialized session data to the database.
     * @param string $id session id
     * @param string $data session data
     */
    public function write($id, $data) : bool// TODO PHP8-REVIEW Type hints missing
    {
        chdir(IL_INITIAL_WD);

        return ilSession::_writeData($id, $data);
    }

    /**
     * Destroys session
     * @param string $id session id
     */
    public function destroy($id) : bool// TODO PHP8-REVIEW Type hints missing
    {
        return ilSession::_destroy($id);
    }

    /**
     * Removes sessions that weren't updated for more than gc_maxlifetime seconds
     * @param int $max_lifetime Sessions that have not updated for the last max_lifetime seconds will be removed.
     */
    public function gc($max_lifetime)
    {
        return ilSession::_destroyExpiredSessions();
    }
}
