#!/bin/bash
#############################################
# Cipi — www-data sudoers whitelist for the panel API
#
# sudo-rs (Ubuntu 25.10+) allows '*' only as the final argument token.
# Use "db restore *" (not "db restore * *") for multi-arg commands.
#############################################

write_cipi_api_sudoers() {
    cat > /etc/sudoers.d/cipi-api <<'SUDOEOF'
www-data ALL=(root) NOPASSWD: /usr/local/bin/cipi app create *, \
                               /usr/local/bin/cipi app edit *, \
                               /usr/local/bin/cipi app delete *, \
                               /usr/local/bin/cipi app logs read *, \
                               /usr/local/bin/cipi app suspend *, \
                               /usr/local/bin/cipi app unsuspend *, \
                               /usr/local/bin/cipi basicauth enable *, \
                               /usr/local/bin/cipi basicauth disable *, \
                               /usr/local/bin/cipi basicauth status *, \
                               /usr/local/bin/cipi deploy *, \
                               /usr/local/bin/cipi alias add *, \
                               /usr/local/bin/cipi alias remove *, \
                               /usr/local/bin/cipi www add *, \
                               /usr/local/bin/cipi www force-to-root *, \
                               /usr/local/bin/cipi www force-from-root *, \
                               /usr/local/bin/cipi www clear *, \
                               /usr/local/bin/cipi www status *, \
                               /usr/local/bin/cipi ssl install *, \
                               /usr/local/bin/cipi ssl force *, \
                               /usr/local/bin/cipi db list, \
                               /usr/local/bin/cipi db create *, \
                               /usr/local/bin/cipi db delete *, \
                               /usr/local/bin/cipi db backup *, \
                               /usr/local/bin/cipi db restore *, \
                               /usr/local/bin/cipi db password *, \
                               /usr/local/bin/cipi-read-app-logs *, \
                               /bin/cat /etc/cipi/apps.json
SUDOEOF
    chmod 440 /etc/sudoers.d/cipi-api 2>/dev/null || true
}
