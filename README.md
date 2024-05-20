# Symfony API side of Mafia Game

The API side of the pet project of a game for a group of friends playing in real-life, using their phones somewhat like "game cards". Each player connects to a lobby with their phone and play together to kill all towns-dpeople or beat the Mafia before it happends. Along the two sides of the fight, there are neutral roles with their own goals, seperate from the main conflict.

I've been inspired by playing with my friends the Town of Salem, and The Jackbox Party Pack. The idea is to incorporate the real-life multiplayer aspect and functionality from Jackbox and the rules of the game from Mafia/Cops and Robbers/Town of Salem.

Known bugs:
  - Users joining lobby with the same name are duplicated if their name ends with a char "1".

To do:
  - Game engine
  - New Roles (Mafia member, Citizen, Police Officer, Jester)
  - Option to rename the roles for custom roleplaying
  - Communication based on Websockets with node.js
  - Kill/Hang voting stage
  - Neutral character functionality of the conficlt

Done:
  - Game sessions have different stages of gameplay (Lobby, Game running)
  - Player can create/join/disconnect a lobby
  - Player can perform votes based on current lobby situation (Ready-up; Vote to kill a player; Skip to next day; Rematch on match end)
