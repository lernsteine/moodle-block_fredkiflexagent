# FredKI Flex Agent (block_fredkiflexagent)

Course block that lets teachers choose which **FredKI** agent is available as a chat in their course.

FredKI is a hosted AI service by lernsteine.com based on [MaxKB](https://github.com/1Panel-dev/MaxKB) v2.
**A FredKI account is required.** The plugin itself is free software under the GNU GPL v3 or later.

Requires the plugin **FredKI Chat** (`local_fredkichat`), which holds the FredKI connection and the chat designs.

## How it works

1. On installation the plugin creates the course custom field **FredKI organisation unit** (`fredki_orgunit`).
   The field is locked and hidden, so only managers and administrators can set it.
2. An administrator enters the name of a FredKI folder in this field for each course.
3. Teachers add the block and choose one of the agents stored in that FredKI folder:
   - **Custom design** – the agent has a design in FredKI Chat and opens in the Moodle-styled chat.
   - **FredKI standard** – the agent has no Moodle design and opens in the original FredKI chat.
   - **Unavailable** – public access is not enabled for the agent in FredKI.
4. Participants of the course see a floating chat button.

The list of agents per organisation unit is cached for 10 minutes. The selection page always loads the current state.
When a chat is opened, FredKI Chat checks that the user can access the course and that the block has selected this agent.

## Diagnostics

Administrators find a diagnostics page on the selection page. It shows the course field, the FredKI folders and the agents
offered to the course. Credentials and tokens are never shown.

## Requirements

- Moodle 4.4 or later
- local_fredkichat 1.2.0 or later

## Privacy

The block stores no personal data. For agents without a Moodle design the original FredKI chat is embedded,
so the browser sends chat messages directly to FredKI.

## License

2026 lernsteine.com – GNU GPL v3 or later.
