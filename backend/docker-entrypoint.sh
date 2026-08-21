#!/bin/sh
set -e

echo "=== Aiven CA check ==="

if [ -f /etc/secrets/ca.pem ]; then
    echo "CA file exists"

    cp /etc/secrets/ca.pem /tmp/aiven-ca.pem

    chmod 644 /tmp/aiven-ca.pem

    echo "CA copied to /tmp/aiven-ca.pem"
    ls -l /tmp/aiven-ca.pem
else
    echo "ERROR: /etc/secrets/ca.pem not found"
fi

exec apache2-foreground
