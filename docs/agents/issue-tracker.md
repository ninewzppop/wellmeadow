# Issue Tracker — wellmeadow

> Created by `/setup-matt-pocock-skills` on 2026-09-10. Single source of truth
> for `to-spec`, `to-tickets`, `code-review`, `triage`.

## Configured tracker: LOCAL FILES (primary)

- **Mode:** local files. `gh` CLI is NOT installed in this environment, so do
  NOT call `gh issue` / `gh label`. If the user later installs `gh` + auth,
  GitHub (`origin: https://github.com/ninewzppop/wellmeadow.git`) becomes the
  mirror — see fallback below.
- **Spec location:** one Markdown file per spec, committed in repo
  (e.g. `.scratch/<feature-slug>/spec.md` or `docs/specs/<slug>.md`).
- **Ticket location:** one file per ticket under
  `.scratch/<feature-slug>/issues/<NN>-<slug>.md`, numbered `01, 02, …`
  in dependency order (blockers first).
- **Ticket template:** see `to-tickets` skill `<local-ticket-template>`.
  Each file MUST contain `**Blocked by:**` (numbers/titles or
  `None (can start immediately)`) and `**Status:** ready-for-agent`.
- **Frontier rule:** work any ticket whose blockers are all done.
  Linear chain = top to bottom.

## Triage label vocabulary (labels are TEXT, not GitHub API)

Applied as `**Status:**` line in local ticket files:

| Label | Meaning | Who applies |
|---|---|---|
| `ready-for-agent` | Agent-grabbable. `to-tickets` applies this by default. `implement` picks these up. | `to-tickets` / `triage` |
| `needs-grill` | Too fuzzy to spec — send back to `grill-with-docs`. | `triage` |
| `needs-research` | Needs runnable/prototype answer first (`prototype` + `handoff`). | `triage` |
| `blocked` | Waiting on its `Blocked by` edges. Do not start. | system (from edges) |
| `done` | Acceptance criteria met + `code-review` passed. | `implement` |

`to-spec` publishes with `ready-for-agent` and skips further triage.
Tickets from `to-tickets` are already agent-ready — do NOT re-`triage` them.

## How skills use this file

- `to-spec` step 3: write spec file here (local path), set `ready-for-agent`.
- `to-tickets` step 5 (Local files branch): write one file per ticket,
  edges as text in `Blocked by`.
- `code-review` step 2 (spec source order): 1) issue refs in commits
  (N/A locally — skip fetch), 2) user-passed path, 3) spec file under
  `docs/`, `specs/`, `.scratch/` matching branch/feature name.
  Standards sources: `CONTRIBUTING.md` / `CODING_STANDARDS.md` if present
  (currently absent — fall back to smell baseline only).

## GitHub fallback (manual, only if `gh` becomes available)

- Remote: `https://github.com/ninewzppop/wellmeadow.git`
- Publish one issue per ticket in dependency order, use native
  blocking / sub-issue links where supported, else `Blocked by` text.
- Recreate the same 5 labels via `gh label create` and apply
  `ready-for-agent` to agent-ready tickets.
- Until then: do everything with local files above.
