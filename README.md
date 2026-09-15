# mod_privatenotes

Private per-user notes inside a Moodle course.

## Behaviour

- A teacher adds the **Private notes** activity near a video, page, lesson or other course content.
- A student opens the activity and writes in the standard Moodle editor.
- The note is stored in `privatenotes_content` using the activity instance id plus the current user id.
- No URL parameter can be used to request another user's note. The content API does not accept a user id at all; it always uses the current Moodle session user.
- Teachers never receive a capability to read note text.

## Teacher metadata

Each activity can expose:

1. no note metadata;
2. aggregate metadata only;
3. individual metadata containing student name plus created/modified dates.

The individual metadata query deliberately does not select the `content` column.

## Backup privacy

Private note content is intentionally **not included in normal course backup files**. This prevents a user who can back up a course from extracting students' private text from the backup archive. The activity configuration and intro are backed up normally.

The Privacy API still allows the owning user to export or delete their own data through Moodle's privacy workflows.

## Requirements

- Moodle 4.1 or later.
- No external services.
- No JavaScript dependency.
