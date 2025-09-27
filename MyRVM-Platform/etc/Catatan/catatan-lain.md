To show databases within a PostgreSQL instance running in a Docker container, follow these steps: Access the PostgreSQL container's shell.
Code

```bash
docker exec -it <container_name_or_id> bash
```

Replace <container_name_or_id> with the actual name or ID of your running PostgreSQL container. Connect to PostgreSQL using psql.
Code

```bash
psql -U <username>

```

Typically, the default username is postgres. If you have a different user, replace <username> accordingly. You might be prompted for a password if one is set for the user. list all databases.
Once connected to the psql prompt, use the \l (backslash followed by lowercase L) meta-command to list all databases:
Code

```bash
    \l
```

This command will display a table showing all databases, their owners, encoding, collation, and ctype.
