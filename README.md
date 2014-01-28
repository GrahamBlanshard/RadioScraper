RadioScraper
============

Fetches, sorts and provides data on radio station playlists provided by some Nielsen BDS Real Time supported stations

Pre-coded with many sites available around Saskatchewan. (Assuming they still work). This was just a waste-of-time project I put together to see just *how much* local stations play songs during the day. Originally hosted on my Koding account but no longer maintain that repository.

Bonus points to all the ways you can point out I'm a terrible web programmer!

This works by taking station_id values to load the playlists available online at a specific URL. Then, using curl it fetches the tabulated data into an array for sorting, allowing you to take whatever statistics on the shown songs you'd like. Pre-coded statistics are for:

- Last time song was played
- Time since the last play
- Total number of times played

With DB support you'd be able to keep a running record of which songs are played most frequently over a period of time as well as try to predict when the song will be played again.
