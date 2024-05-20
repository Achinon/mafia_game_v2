The purpose of this personal project is to get more familiar with Symfony.

I got the idea by playing with my friends the Town of Salem, and Jackbox Party box. The idea is to incorporate the real-life multiplayer aspect and functionality 

Known bugs:
  - Users joining lobby with the same name are duplicated if their name ends with a char "1".

To do:
  - Game engine
  - New Roles (Mafia member, Citizen, Police Officer, Jester)
  - Option to rename the roles for custom roleplaying
  - Communication based on Websockets with node.js

Done:
  - Game sessions have different stages of gameplay (Lobby, Game running)
  - Player can create/join/disconnect a lobby
  - Player can perform votes based on current lobby situation (Ready-up; Vote to kill a player; Skip to next day; Rematch on match end)
