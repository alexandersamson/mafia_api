<?php


class JsonPostValidationController
{

    function validate($postData)
    {
        if(!isset($postData["publicApiKey"])) {
            ?>
            <div style='background-color:#D0D0D0; font-family:arial,sans-serif; padding:22px;'>
            <img src="../media/mafia-online-game-api-logo.png" alt="" width="720"></img><br>
            Hi!<br>
            <br>
            This is the public <b>Mafia API</b>!<br>
            Please use the following API key for API access:<br><br> <b><?php echo GlobalsService::getInstance()->getApiKey(); ?></b><br>
            <br>
            <u>Usage of the API:</u><br> 
            {<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"publicApiKey": "<i><?php echo GlobalsService::getInstance()->getApiKey(); ?></i>",<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"request": "<i>create_player</i>",<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"payload": <i>{"name": "Johnny_Doe"}</i><br>
            }<br><br>
                <form action="/index.php" method="post">
                    <label for="texttest"></label>
                    <textarea id="texttest" rows="8" cols="100" name="api" >{&quot;publicApiKey&quot;:&quot;<?php echo GlobalsService::getInstance()->getApiKey(); ?>&quot;,&quot;request&quot;:&quot;get_joinable_games_page&quot;,&quot;payload&quot;:{&quot;page&quot;:1}}
                    </textarea><br>
                    <input type="submit" value="Test API">
                </form>
            <br>
            <u>All</u> API calls to this Mafia API are made with <b>POST</b> calls, even 'get', 'put' and 'delete' routines.<br>
            Publicly available API requests are listed below:<br>
            <br>
            <?php
            $requests = GlobalsService::getInstance()->getApiRequests();
            ksort($requests);
            foreach ($requests as $key => $request){
                echo "<b>".(string)$key."</b><br>";
                if(is_array($request)){
                    foreach ($request as $subKey => $item){
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;".(string)$subKey."<br>";
                        if(is_array($item)){
                            foreach ($item as $k => $v){
                                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".(string)$k."&nbsp;(<i>".$v."</i>)<br>";
                            }
                        } else {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . (string)$item."<br>";
                        }
                    }
                } else {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;".(string)$request."<br>";
                }
                echo "<br>";
            }
            echo "</div>";
            exit();
        }
        if ($postData["publicApiKey"] == GlobalsService::getInstance()->getApiKey()) {
            if(
                isset($postData["request"]) &&
                isset($postData["payload"])
            ){
                return true;
            } else{
                MessageService::getInstance()->add("error","Corrupted JSON data: Missing request and/or payload");
            }
        } else {
            MessageService::getInstance()->add("userError", "Your API Key is invalid. Please use a valid API Key.");
            MessageService::getInstance()->add("error", "Request aborted: No valid app key provided");
            print_r(JsonBuilderService::getInstance()->ConsumeJson());
            exit();
        }
        return false;
    }

}