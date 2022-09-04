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

All log files are stored in an "incoming" directory where the queue command will scan, deduplicate, and prep the file for being parsed.

```
./gamelogs logs:queue
```

### Parse Log File

Once a log file has been queued, the parsing command will take over. The parser will automatically determine the game/mod contained withing the log file and call the appropriate class file configured for that spaecific game.

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

