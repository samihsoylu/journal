ARG JOURNAL_IMAGE=journal-platform-candidate:local
FROM ${JOURNAL_IMAGE}

# Test-only image. The release image never contains a coverage extension.
USER root
# Use the pinned upstream source: PECL's download endpoint is often unavailable.
ADD --checksum=sha256:fdd07cad8e2ff42f0c9f095d84aeef11dab0fde7a008805f61883cbcb1b3f12b https://codeload.github.com/krakjoe/pcov/tar.gz/refs/tags/v1.0.12 /tmp/journal-pcov.tar.gz
RUN mkdir /tmp/journal-pcov && \
    tar -xzf /tmp/journal-pcov.tar.gz --strip-components=1 -C /tmp/journal-pcov && \
    install-php-extensions /tmp/journal-pcov && \
    rm -rf /tmp/journal-pcov /tmp/journal-pcov.tar.gz
ENTRYPOINT []
CMD ["php", "-d", "memory_limit=1G", "-d", "pcov.directory=/var/www/html/private", "vendor/bin/phpunit", "--coverage-text"]
