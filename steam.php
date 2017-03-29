<?php
require_once("openid.php");

/*
The MIT License (MIT)

Copyright (c) 2017

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/*
# Name: Steam
# Description:
    Establish connection to Steam servers. Allows the use of Steam API services.
# Libraries:
    LightOpenID - https://github.com/iignatov/LightOpenID;
# Documentations
    SteamAPI - https://developer.valvesoftware.com/wiki/Steam_Web_API
*/

class Steam{
  // Attributes
  private $_openID = null,
          $_apiKey = "" ;

  protected $api = ["fqdn" => "https://api.steampowered.com/",
                    "news" => "ISteamNews",
                    "user" => "ISteamUser",
                    "stats" => "ISteamUserStats",
                    "player" => "IPlayerService" ];

  // Builders
  public function __construct($website, $apiKey){
    $this->_OpenID = new LightOpenID($website);
    $this->_OpenID->identity = "https://steamcommunity.com/openid/";
    $this->_apiKey = $apiKey;
  }

  // Private Methods
  private function curl($url){
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }

  // Returns uri of the latest of a game specified by its appID.
  private function getNewsForApp($appID, $count, $maxLength){
    return $this->api["news"]."/getNewsForApp/v0002/?appid=$appID&count=$count&maxlength=$maxLength";
  }

  // Returns uri of global achievements overview of a specific game in percentages.
  private function getGlobalAchievementPercentages($appID){
    return $this->api["stats"]."/GetGlobalAchievementPercentagesForApp/v0002/?gameid=$appID";
  }

  // Returns uri for basic profile information for a list of 64-bit Steam IDs.
  private function getPlayerSummaries($steamID){
    $ids = (is_array($steamID)) ? join(",", $steamID) : $steamID;
    return $this->api["user"]."/GetPlayerSummaries/v0002/?steamids=$ids";
  }

  // Returns uri of the friend list of any Steam user, provided his Steam Community profile visibility is set to "Public".
  private function getFriendList($steamID, $relationship = "friend"){
    return $this->api["user"]."/GetFriendList/v0001/?steamid=$steamID&relationship=$relationship";
  }

  // Returns uri of a list of achievements for this user by app id
  private function getPlayerAchievements($steamID, $appID){
    return $this->api["stats"]."/GetPlayerAchievements/v0001/?steamid=$steamID&appid=$appID";
  }

  // Returns uri of a list of achievements for this user by app id
  private function getUserStatsForGame($steamID, $appID){
    return $this->api["stats"]."/GetUserStatsForGame/v0002/?steamid=$steamID&appid=$appID";
  }

  // Returns uri of a list of achievements for this user by app id
  private function getOwnedGames($steamID){
    return $this->api["player"]."/GetOwnedGames/v0001/?steamid=$steamID";
  }

  /* Returns uri for a list of games a player owns along with some playtime
  information, if the profile is publicly visible. Private, friends-only, and
  other privacy settings are not supported unless you are asking for your own
  personal details */
  private function getRecentlyPlayedGames($steamID){
    return $this->api["player"]."/GetRecenltyPlayedGames/v0001/?steamid=$steamID";
  }

  /* Returns uri of the original owner's SteamID if a borrowing account is
  currently playing this game. If the game is not borrowed or the borrower
  currently doesn't play this game, the result is always 0.*/
  private function isPlayingSharedGame($steamID, $appID){
    return $this->api["player"]."/IsPlayingSharedGame/v0001/?steamid=$steamID&appid_playing=$appID";
  }

  // Returns uri of game-name, game-version and available game-stats.
  private function getSchemaForGame($appID){
    return $this->api["stats"]."/GetSchemaForGame/v2/?appid=$appID";
  }

  // Returns uri of ban statuses for given players. (Community, VAC, Economy)
  private function getPlayerBans($steamID){
    $ids = (is_array($steamID)) ? join(",", $steamID) : $steamID;
    return $this->api["user"]."/GetPlayerBans/v1/?steamids=$ids";
  }

  private function validateArguments($arg, $value){
      if(!($arg == $value)) die("Missing arguments");
  }
  // Public Methods

  /*
  Returns requested JSON data.
  Throws exception on invalid service.
 */
  public function get($type){
    $url = $this->api["fqdn"];
    $arg = func_get_args();
    $numArgs = func_num_args();
    switch($type){
      case "newsForApp":
        $this->validateArguments($numArgs, 4);
        $url .= $this->getNewsForApp($arg[1],$arg[2],$arg[3]);
        return file_get_contents(urlencode($url));
      case "globalAchievementPercentages":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getGlobalAchievementPercentages($arg[1]);
        return file_get_contents(urlencode($url));
      case "playerSummaries":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getPlayerSummaries($arg[1]);
        break;
      case "friendList":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getFriendList($arg[1],$arg[2]);
        break;
      case "playerAchievements":
        $this->validateArguments($numArgs, 3);
        $url .= $this->getPlayerAchievements($arg[1],$arg[2]);
        break;
      case "userStatsForGame":
        $this->validateArguments($numArgs, 3);
        $url .= $this->getUserStatsForGame($arg[1],$arg[2]);
        break;
      case "ownedGames":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getOwnedGames($arg[1]);
        break;
      case "recentlyPlayedGames":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getRecentlyPlayedGames($arg[1]);
        break;
      case "isPlayingSharedGame":
        $this->validateArguments($numArgs, 3);
        $url .= $this->isPlayingSharedGame($arg[1],$arg[2]);
        break;
      case "schemaForGame":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getSchemaForGame($arg[1]);
        break;
      case "playerBans":
        $this->validateArguments($numArgs, 2);
        $url .= $this->getPlayerBans($arg[1]);
        break;
      default:
        throw new Exception("Steam: Unknown api service \"$type\".");
    }

    $url .= "&key=".$this->_apiKey;
    return $this->curl($url);
  }

  /* Allows user authentication.
  Takes callback with 1 array argument to handle logged user basic information.
  Tip: Use it for database registration. */
  public function login($returnTo, $callback = null, $createSession = true){
    // Attempt to connect to steam servers and log user in.
    try{
      if(!$this->_OpenID->mode){ // Attempt to login
        header('Location: ' . $this->_OpenID->authUrl());
      }

      // Try login validation
      if($this->_OpenID->validate()){
        preg_match("/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $this->_OpenID->identity, $regexMatches);
        $account["id"] = $regexMatches[1];

        // Get account basic information
        $parsedJson = json_decode($this->get("playerSummaries", $account["id"]), true);
        $usr = $parsedJson["response"]["players"]["0"];

        // Execute callback function
        if(is_callable($callback)) $callback($usr);

        if($createSession){
          if(session_status() == PHP_SESSION_NONE) session_start();

          $_SESSION["steam_auth"] = [ "nickname" => $usr["personaname"], "steamProfile" => $usr["profileurl"], "avatar" => $usr["avatar"]];
        }

        header("Location: $returnTo");
      }

      return false; // Login Failed
    }catch(Exception $e){
      die("Error: ".$e);
    }
  }

}
?>
