                                                        _
                                                       | |
                              __ _  __ _ _ __ ___   ___| | ___   __ _ ___
                             / _` |/ _` | '_ ` _ \ / _ \ |/ _ \ / _` / __|
                            | (_| | (_| | | | | | |  __/ | (_) | (_| \__ \
                             \__, |\__,_|_| |_| |_|\___|_|\___/ \__, |___/
                              __/ |                              __/ |
                             |___/                              |___/


A simple stand-alone command-line utility for parsing idtech  based log files such as those used with Quake III mods.

## Usage

### Queue Log Files

All log files are stored in an "incoming" directory where the queue command will scan, de-duplicate, and prep the file for being parsed.

```
./gamelogs logs:queue
```

#### Example

```
$ ./gamelogs logs:queue
13 Game logs to Queue
Checking Q3_1.31.log: ✔
Queuing Q3_1.31.log: ✔
Checking games copy.log: ✔
Queuing games copy.log: ✔
Checking games.log: ✔
Queuing games.log: ✔
Checking ioq3.log: ✔
Queuing ioq3.log: ✔
Checking ioq3_1.36.log: ✔
Queuing ioq3_1.36.log: ✔
Checking q3-67.log: ✔
Queuing q3-67.log: ✔
Checking q3_1.11.log: ✔
Queuing q3_1.11.log: ✔
```

### Parse Log File

Once a log file has been queued, the parsing command will take over. The parser will automatically determine the game/mod contained withing the log file and call the appropriate class file configured for that spaecific game.

The parser will also attempt to de-duplicate individual matches and ignore incomplete ones.

You can parse all log files or provide the log hash to target a specific one.
```
./gamelogs logs:parse
./gamelogs logs:parse <log-hash>
```

Each game's match is parsed in to a SQLite database and its data organized in to:
- Matches
- Players
- Game Activity

  -- Kills

  -- Item Collection

  -- Chats

  -- Player Joined/Left


#### Example

```
$ ./gamelogs logs:parse
Parse Log: c5ee281adfa06af4933552eb5fc9a6be.log: ✔
Parse Log: cccd3b15698b87a211f7699fd9aba5c5.log: ✔
Parse Log: e88abe78e689bb81e330080eaa0598af.log: ✔
```

## Installation (Development)

Gamelogs uses [Laravel-Zero](https://github.com/laravel-zero/laravel-zero).

1. Clone this repo
2. Install dependencies `composer install`
3. Copy `.env.example` to `.env` and set values to your preferecnes.
4. Create database: `./gamelogs db:migrate --seed`

### Game Seeder

The `GamesSeeder` class contains supported games. Look at examples to see what it's doing - it's pretty straight forward. The `identfiers` key is what will be parsed from the log file to determine the game.

### Parsers

Each game has a parser class in `App\Parsers`. Parsers should extend a base game engine parser for reusibilty between games.

## Installation (Server Admins)

Not sure yet :D. Laravel Zero supports stand-alone phar archives but this is untested. Stay tuned.

## Game Support

Gamelogs currently supports idTech3 based games (Quake 3, JediKnight II, etc).
