# Security fix: task authorization

## Summary

Fixed an insecure direct object reference (IDOR) in nested task routes.
Authenticated users could change tasks belonging to other users by combining
their own project ID with another user's task ID. Task reassignment and deletion
had related authorization problems.

This was a focused repair, not a full security audit.

## What was wrong

Routes use the structure `/project/{project}/task/{task}`. The existing
`OwnerCheck` middleware verifies ownership of the project in the URL, but that
does not establish that the requested task belongs to that project.

- **Updates:** `update()` looked up tasks globally by ID. A user could supply
  their own project in the URL and another user's task ID to modify or move
  that task.
- **Moves:** the destination `project_id` was also looked up globally.
  A user could move a task into a project owned by someone else.
- **Deletion:** `destroy()` accepted only one route argument, although the
  nested route supplies both project and task IDs. It treated the project ID
  as the task ID, potentially deleting an unrelated task, including another
  user's task when the IDs coincided.
- **View/edit:** these methods already checked whether the task matched the
  URL project. Their lookups now use the same scoped approach as updates and
  deletion.

## What changed

Updated [ProjectTaskController.php](app/Http/Controllers/ProjectTaskController.php):

1. `edit()`, `update()`, and `destroy()` first find the URL project through the
   authenticated user's `projects()` relationship.
2. They then find the task through that project's `tasks()` relationship.
   `show()` inherits this check because it delegates to `edit()`.
3. When moving a task, `update()` resolves the destination through the user's
   own projects before changing the task.
4. `destroy($projId, $taskId)` now accepts both route arguments and deletes
   only the task selected within the authorized project.

The controller returns HTTP 404 when a scoped lookup fails, without modifying
the task or decrementing the deletion counter. Existing middleware behavior
still applies before the controller runs. For mismatched view/edit task IDs,
the controller response changed from HTTP 403 to HTTP 404.

Owners can still view, update, delete, and move tasks between their own projects.
After a move, the old project URL cannot be used to update that task.

The initial security repair did not change routes, database schema,
dependencies, or configuration. The subsequent runtime migration is documented
in [upgrade.md](upgrade.md).

## Regression coverage

Added [ProjectTaskAuthorizationTest.php](tests/ProjectTaskAuthorizationTest.php).
It uses an in-memory SQLite database and mocks Redis deletion-counter calls.
The HTTP tests cover:

- Show, edit, PUT, PATCH, and DELETE requests with a task from another project,
  including projects belonging to the same user and to another user.
- Rejected moves to another user's project or a nonexistent project.
- Valid owner reads, updates, moves, and deletion.
- Rejection of updates through the old URL after moving a task.
- Deletion with colliding project/task IDs and with a nonexistent task ID.
- Unchanged task data and deletion counters for rejected operations.

## Initial validation results

| Check | Result |
| --- | --- |
| Isolated controller checks | 18 passed after the fix |
| PHP syntax checks for the controller and test file | Passed |
| `git diff --check` | Passed |
| PHPUnit HTTP regression tests | Blocked: `vendor/bin/phpunit` is missing |

The isolated checks used a temporary harness with framework/model doubles.
It reproduced unauthorized updates, moves, and deletion before the fix.
The harness was removed after validation. These checks do not replace running
the HTTP tests against Laravel.

At the time of the initial repair, the environment had PHP 8.5, while the
project targeted Laravel 5.2 and PHPUnit 4. Dependencies were not installed.

## Validation after the runtime upgrade

The application now targets PHP 8.5, Laravel 13, and PHPUnit 12. Dependencies
have been installed, and all 17 task authorization HTTP regression cases pass
against Laravel using the real router, middleware, and Eloquent relationships.
The missing-dependency blocker above is resolved.

With PDO SQLite enabled, run from the repository root:

```sh
composer test -- --filter ProjectTaskAuthorizationTest
```
