# .scratch — local issue tracker workspace

This directory is the LOCAL tracker configured by `/setup-matt-pocock-skills`
(see `docs/agents/issue-tracker.md`).

Layout:

```
.scratch/<feature-slug>/
  spec.md                # output of to-spec (optional)
  issues/
    01-<slug>.md         # output of to-tickets, one file per ticket
    02-<slug>.md
```

Rules:

- Numbered `01, 02, …` in dependency order (blockers first).
- Each ticket file uses the `to-tickets` `<local-ticket-template>`:
  `What to build` + `Blocked by` + `Status: ready-for-agent` + acceptance checkboxes.
- Work the frontier: any ticket whose blockers are done.
- Tickets from `to-tickets` are agent-ready — do NOT re-triage.
- This README is the only committed file; feature dirs are created per effort.
- `gh` CLI is absent, so GitHub is manual mirror only.
