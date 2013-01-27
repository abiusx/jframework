<?php
    define ("jF_Plugin_jChat_Path","plugin.jchat");
    define ("jF_Plugin_jChat_MessagesTable","jfp_jchat");
    define ("jF_Plugin_jChat_UsersTable","jfp_jchat_users");
    define ("jF_Plugin_jChat_MaxMessages","25"); //Maximum number of messages to hold for each channel
    define ("jF_Plugin_jChat_NoActivity","600"); //Maximum number of seconds a user can be inactive
    define ("jF_Plugin_jChat_UpdateRatio",".1"); //how often to update
    
    
    
    /**
     * The jChat plugin for jFramework
     * Version 0.0.1
     * 
     *  Use Cases:
     * Join a chat channel (Join)
     * Send message (Send)
     * Receive new messages (Receive)
     * Leave a chat channel (Leave)
     * Receive list of channel users (ChannelUsers)
     */
    class jChat extends BasePluginClass 
    {
        /**
         * Returns the chatter info if joined the channel
         *
         * @param Integer $Channel Channel Number
         * @return array UserInfo on success, False on not joined 
         */
        function Current($Channel)
        {
            $Result=$this->DB->Execute("SELECT * FROM `".jF_Plugin_jChat_UsersTable."`".
            " WHERE SessionID=? AND ChannelID=?",session_id(),$Channel);
            if ($Result)
                return $Result[0];
            else 
                return false;
        }
        /**
         * Checks wheter a nickname exists on a channel or not
         *
         * @param Integer $Channel Channel Number
         * @param String $Nickname User Nickname
         * @return UserInfo on exists, false on not exists
         */
        function NicknameExists($Channel,$Nickname)
        {
            $Result=$this->DB->Execute("SELECT * FROM `".jF_Plugin_jChat_UsersTable."`".
            " WHERE ChannelID=? AND Nickname=?",$Channel,$Nickname);
            if ($Result)
                return $Result[0];
            else 
                return false;
        }
        /**
         * Adds a user to a channel
         *
         * @param String $Nickname Nickname of the chatter
         * @param Integer $Channel Channel Number
         * @return 0 on success, -1 on user already in channel, -2 on nickname already on channel
         */
        function Join($Nickname,$Channel)
        {
            if ($this->Current($Channel)) return -1;
            if ($this->NicknameExists($Channel,$Nickname)) return -2;
            $this->DB->Execute("INSERT INTO `".jF_Plugin_jChat_UsersTable."` ".
            "(Nickname,JoinTimestamp,ChannelID,SessionID,AccessTimestamp) VALUES (?,?,?,?,?)",
                ($Nickname),date("Y-m-d H:i:s"),$Channel,session_id(),date("Y-m-d H:i:s"));
            return 0;                
        }
        /**
         * Removes a user from a channel
         *
         * @param Integer $Channel Channel Number
         * @return false on user not in channel, true on success
         */
        function Leave($Channel)
        {
            if (!$this->Current($Channel)) return false;
            $this->DB->Execute("DELETE FROM `".jF_Plugin_jChat_UsersTable."` WHERE ".
            "SessionID=? AND ChannelID=?",session_id(),$Channel);
            return true;
        }
        /**
         * List of channel users
         *
         * @param Integer $Channel Channel Number
         * @return Array(Array(ID,Nickname,JoinTimestamp,AccessTimestaamp))
         */
        function ChannelUsers($Channel)
        {
            $Result=$this->DB->Execute("SELECT ID,Nickname,JoinTimestamp,AccessTimestamp FROM `".jF_Plugin_jChat_UsersTable."` ".
            " WHERE ChannelID=? ORDER BY Nickname",$Channel);
            return $Result;
        }
        /**
         * Adds a message to the channel
         *
         * @param String $Message the chat message
         * @param Integer $Channel Channel Number
         * @return ID of the message on success, false on user not logged in
         */
        function Send($Message,$Channel)
        {
            if (!$this->Current($Channel)) return false;
            $Res=$this->DB->Execute("INSERT INTO `".jF_Plugin_jChat_MessagesTable."` ".
            "(ChannelID,Message,Timestamp,ChatterID) VALUES (?,?,?,".
            " ( SELECT ID FROM `".jF_Plugin_jChat_UsersTable."` WHERE SessionID=? AND ChannelID=?)".
            ")",$Channel,$Message,date("Y-m-d H:i:s"),session_id(),$Channel
            );
            $this->_Update($Channel);
            return $Res;
        }
        /**
         * Receives new messages 
         *
         * @param Integer $Channel Channel Number
         * @param Integer $LastID Last Message already received, to receive newers
         * @return Array(Array(ID,Message,Timestamp,ChatterID,Nickname))
         */
        function Receive($Channel,$LastID)
        {
            $Result=$this->DB->Execute("SELECT T1.ID AS ID ,Message,Timestamp,ChatterID,T2.Nickname AS Nickname FROM ".
            "`".jF_Plugin_jChat_MessagesTable."` AS T1 JOIN `".jF_Plugin_jChat_UsersTable."` AS T2 ON ".
            "(T1.ChatterID=T2.ID) ".
            "WHERE T1.ChannelID=? AND T1.ID>?",$Channel,$LastID);
            $this->_Update($Channel);
            return $Result;   
        }
        /**
         * This function sweeps the chat. Leaves only a constant number of messages
         * lying on the server, and also unjoins the users which havent been actiev for a period of time
         * This also updates the last access time of users to a more recent time.
         * 
         * @param Integer $Channel Channel Number
         */
        private function _Update($Channel)
        {
            if (rand(0,1000)/1000>jF_Plugin_jChat_UpdateRatio) return;
            $Res=$this->DB->Execute("SELECT MAX(ID) FROM `".jF_Plugin_jChat_MessagesTable."` WHERE 1=?","1");
            $count=$Res[0]["MAX(ID)"];
            $this->DB->Execute("DELETE FROM `".jF_Plugin_jChat_MessagesTable."` WHERE ChannelID=? ".
            "AND ID<?-?",$Channel,$count,jF_Plugin_jChat_MaxMessages);
            $this->DB->Execute("DELETE FROM `".jF_Plugin_jChat_UsersTable."` WHERE ".
            " AccessTimestamp<?",date("Y-m-d H:i:s",time()-jF_Plugin_jChat_NoActivity));
            $this->DB->Execute("UPDATE `".jF_Plugin_jChat_UsersTable."` SET AccessTimestamp=? ".
            "WHERE ChannelID=? AND SessionID=?",Date("Y-m-d H:i:s"),$Channel,session_id());             
        }
        
        function WhoAmI($Channel,&$LastID)
        {
             $Res=$this->Current($Channel);
             $LastID=$this->LastID($Channel);
             return $Res;   
        }
        function LastID($Channel)
        {
            $Res=$this->DB->Execute("SELECT MAX(ID) FROM `".jF_Plugin_jChat_MessagesTable."` ".
                    "WHERE ChannelID=?",$Channel);
            if ($Res)
                return $Res[0]["MAX(ID)"];
            else
                return false;
        }
        
        
    }
?>