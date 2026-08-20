---
name: domain-modeling
description: Turns a sharpened specification into a precise domain model - entities, value objects, relationships, invariants, and how they map to persistence. Use after requirements are pinned down, before writing migrations or Laravel models.
---

# Domain-Modeling

## Purpose

Domain-modeling converts a sharpened specification into the backbone of the
implementation: a clear model of the "things", their relationships, and their
rules, mapped to how they will be stored and queried.

## Inputs

A grilling interview summary (or any sharpened spec). If requirements are
still fuzzy, do not model yet — go back and grill.

## Workflow

### 1. Extract the entities

List every noun with identity and history. For each: name, purpose, and the
attributes that make it distinct. Question each candidate: is this an entity
(own identity, changes over time) or a value object (data describing
something else, interchangeable)?

### 2. Define value objects vs entities

Mark each one. Value objects: no own lifecycle, compare by value, belong to an
entity (e.g. Address, Phone, Money). Entities: have an ID and a history (e.g.
Staff, Ward, Qualification).

### 3. Draw the relationships

For every pair of entities: cardinality (one-to-many, many-to-many, optional?),
ownership/aggregate root, and whether the link needs data of its own (which
turns it into an entity — e.g. an allocation needs a date, so it is not a bare
link).

### 4. Pin the invariants

List every rule that must always hold ("a ward must have at least one trained
nurse on every shift", "a staff member cannot be allocated to two wards with
overlapping shifts"). These become constraints, validation, and tests.

### 5. Map to persistence

For a relational target: translate entities to tables, value objects to
columns or embedded tables, relationships to foreign keys / pivot tables.
Decide nullable columns and indexes based on the searches and reports from the
spec. Note the exact searchable fields (exact vs partial match drives index
choice).

### 6. Walk the lifecycle against the model

Re-verify the model against every lifecycle step from the spec (create, edit,
delete, search, report). Any mismatch is a modeling bug — fix it now, not in
code.

### 7. Deliverables

Write the model out as:

- **Entity list** — table: Entity | Type | Key attributes | Lifecycle
- **Relationships** — every pair with cardinality and ownership
- **Invariants** — numbered list
- **Persistence map** — tables, columns, keys, indexes, with search/report
  queries they support
- **Glossary** — every domain term and its agreed definition

## Definition of done

The model answers, from the tables alone, every search and report the spec
asks for. No spec requirement is left unmapped. No rule requires
application-code heroics that a schema constraint could have expressed.
