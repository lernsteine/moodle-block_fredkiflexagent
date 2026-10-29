# Changes

## 2.2.0 (2026-10-28)

- Licensed under GNU GPL v3 or later; coding style follows the Moodle coding standard.
- Uses the FredKI connection of local_fredkichat; no own FredKI client and no reading of other plugins' settings.
- Agents per organisation unit are cached, course pages no longer contact FredKI on every view.
- The course field "fredki_orgunit" is created automatically, locked and hidden.
- Selection page checks the capability in the block context of the selected block.
- Diagnostics are limited to site administrators.
- Privacy API declares the data sent to FredKI by embedded original chats.
