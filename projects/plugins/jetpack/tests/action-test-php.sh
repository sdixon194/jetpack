#!/bin/bash

set -eo pipefail

# Todo: if WooCommerce is installed, allow the Jetpack integration tests to run.
if [[ "$WP_BRANCH" == "latest" && -d "/tmp/wordpress-$WP_BRANCH/src/wp-content/plugins/woocommerce" ]]; then
	echo "Todo: Woo/JP integration tests should run."
	export JETPACK_TEST_WOOCOMMERCE=1
fi

echo "::group::Jetpack tests"
phpunit
echo "::endgroup::"

if [[ "$WP_BRANCH" == "trunk" ]]; then
	echo "::group::Jetpack multisite tests"
	WP_MULTISITE=1 phpunit -c tests/php.multisite.xml
	echo "::endgroup::"
fi

if [[ "$WP_BRANCH" == "latest" && "$PHP_VERSION" == "7.0" ]]; then
	echo "::group::Jetpack Legacy Full Sync tests"
	LEGACY_FULL_SYNC=1 phpunit --group=legacy-full-sync
	echo "::endgroup::"
fi
