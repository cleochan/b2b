<?php

class Algorithms_Core_Feed
{
    var $user_id_array; //array
    
    function Publish()
    {
        if(count($this->user_id_array))
        {
            foreach($this->user_id_array as $user_id)
            {
                $this->CollectFeedInfo($user_id);
            }
        }else{
            $result = "Error: Key parameter missed.";
        }
        
        return $result;
        
    }
    
    function CollectFeedInfo($user_id)
    {
        $users_feed_model = new Databases_Tables_UsersFeed();
        $users_feed = $users_feed_model->GetFeedInfo($user_id);
        
        if($users_feed['users_feed_id'])
        {
            $users_feed_definition_model = new Databases_Tables_UsersFeedDefinition();
            $users_feed_definition = $users_feed_definition_model->GetFeedInfo($users_feed['users_feed_id']);
        }
        
        return array("users_feed" => $users_feed,
                            "users_feed_definition" => $users_feed_definition
                            );
    }
    
    
}