<?php
/**
 * Structured data (reference/nextjs/lib/seo.ts). Offers are only emitted for variants with a real price.
 *
 * @package Priniti
 */

defined( 'ABSPATH' ) || exit;

function priniti_json_ld( array $data ): void {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

function priniti_organization_json_ld(): array {
	$config = priniti_site_config();
	return array_filter(
		array(
			'@context'  => 'https://schema.org',
			'@type'     => 'Organization',
			'name'      => $config['name'],
			'legalName' => $config['legal_name'],
			'url'       => home_url( '/' ),
			'logo'      => $config['logo']['src'] ?? null,
		)
	);
}

function priniti_website_json_ld(): array {
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => priniti_site_config()['name'],
		'url'             => home_url( '/' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => priniti_url( '/shop' ) . '?q={search_term_string}',
			'query-input' => 'required name=search_term_string',
		),
	);
}

function priniti_product_json_ld( array $p ): array {
	$priced = array_values( array_filter( $p['variants'], static fn ( $v ) => null !== $v['price'] ) );
	return array_filter(
		array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $p['name'],
			'brand'       => array( '@type' => 'Brand', 'name' => priniti_site_config()['name'] ),
			'category'    => $p['categoryName'],
			'url'         => $p['href'],
			'image'       => array_column( $p['images'], 'src' ) ?: null,
			'description' => $p['description'] ?: null,
			'offers'      => $priced ? array_map(
				static fn ( $v ) => array_filter(
					array(
						'@type'         => 'Offer',
						'priceCurrency' => priniti_site_config()['commerce']['currency'],
						'price'         => $v['price'],
						'sku'           => $v['sku'] ?: null,
						'availability'  => false === $v['inStock'] ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
						'url'           => $p['href'],
					)
				),
				$priced
			) : null,
		)
	);
}
