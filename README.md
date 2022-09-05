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

```
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

## Installation

TBD

## Game Support

Gamelogs currently supports idTech3 based games (Quake 3, JediKnight, etc).
