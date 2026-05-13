# Dependency overrides

## fast-uri

Temporary npm override:

```json
"overrides": {
  "fast-uri": "3.1.2"
}
```

### Reason:

fast-uri <= 3.1.1 has high severity advisories reported by npm audit.

This dependency is currently transitive, probably via ajv / Angular tooling.

Removal condition:

Try removing this override after Angular / Angular CLI / related tooling updates have naturally resolved fast-uri to a safe version.

Check with:
