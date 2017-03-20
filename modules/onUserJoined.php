<?php

$onUserJoined = function (int $who, array $array) {

    $bot = actionAPI::getBot();

    if ($who >= 1900000000) {
        return;
    }

    $bot->users[$who] = new User($array);
    $user = $bot->users[$who];

    if ($user->isAway()) {
        dataAPI::set('away_' . $user->getID(), true);
    }

    if ($user->isRegistered() && !$user->wasHere() && !dataAPI::is_set('away_' . $user->getID())) {
        $bot->network->sendTickle($who);
    }

    if (!dataAPI::is_set('active_' . $who)) {
        dataAPI::set('active_' . $who, time());
    } else {
        if (dataAPI::is_set('left_' . $who)) {

            if (dataAPI::get('left_' . $who) < time() - 30) {
                dataAPI::set('active_' . $who, time());
            }

            dataAPI::un_set('left_' . $who);
        }
    }
    
    if (dataAPI::is_set('gamebanrelog_' . $who) && !$user->isGamebanned()) {
        dataAPI::un_set('gamebanrelog_' . $who);
    }
        
    if ($user->isGamebanned() && $bot->botData['gameban_unban'] == 2) {
        if (!dataAPI::is_set('gamebanrelog_' . $who)) {
            dataAPI::set('gamebanrelog_' . $who, 0);
        } else {
            dataAPI::set('gamebanrelog_' . $who, dataAPI::get('gamebanrelog_' . $who) + 1);
        }
        if (dataAPI::get('gamebanrelog_' . $who) >= 2) {
            dataAPI::un_set('gamebanrelog_' . $who);
            $powers = xatVariables::getPowers();
            $bot->network->unban($who);
            $bot->network->sendMessage("{$user->getRegname()} signed out and in twice to get unbanned from the gameban '{$powers[$array['w']]['name']}'.");
        }
    }
    
    return;
};
