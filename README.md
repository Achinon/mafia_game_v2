# Symfony API side of Mafia Game

The API side of the pet project of a game for a group of friends playing in real-life, using their phones somewhat like "game cards". Each player connects to a lobby with their phone and play together to kill all towns-dpeople or beat the Mafia before it happends. Along the two sides of the fight, there are neutral roles with their own goals, seperate from the main conflict.

I've been inspired by playing with my friends the Town of Salem, and The Jackbox Party Pack. The idea is to incorporate the real-life multiplayer aspect and functionality from Jackbox and the rules of the game from Mafia/Cops and Robbers/Town of Salem.

### Known bugs:
  - Users joining lobby with the same name are duplicated if their name ends with a char "1"

### To do:
  - Role functionality
  - Communication based on Websockets with node.js
  - Live communication of any kind
  - Cronjobs / Scheduler

### Additional notes on setup:

After performing the migration, for a fully working flow, a one of the required commands is required to add existing roles into the table:
```php bin/console app:populate-roles-table``` 
or 
```php bin/console doctrine:fixtures:load```

### Documentation:

https://documenter.getpostman.com/view/14431758/2sA3QniEfo
