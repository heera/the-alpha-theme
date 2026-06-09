<?php
/**
 * Header — sidebar, brand, navigation, theme toggle.
 *
 * @package TheAlpha
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="page-loader" aria-hidden="true">
	<div class="page-loader__ring"></div>
	<p class="page-loader__label"><span class="page-loader__text"><?php esc_html_e( 'Loading', 'the-alpha' ); ?></span><span class="page-loader__dots"><span>.</span><span>.</span><span>.</span></span></p>
</div>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'the-alpha' ); ?></a>
<div class="scroll-progress" aria-hidden="true"></div>

<header class="topbar">
	<div class="topbar__row">
		<a class="topbar__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		<div class="topbar__actions">
			<button class="topbar__search-toggle" type="button" aria-label="<?php esc_attr_e( 'Toggle search', 'the-alpha' ); ?>" aria-controls="topbar-search" aria-expanded="false">
				<svg class="icon-search" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
				<svg class="icon-search-close" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			</button>
			<button class="nav-toggle" type="button" aria-label="<?php esc_attr_e( 'Toggle menu', 'the-alpha' ); ?>" aria-controls="sidebar" aria-expanded="false">
				<span></span>
			</button>
		</div>
	</div>
	<div class="topbar__search" id="topbar-search" hidden>
		<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="the-alpha-topbar-s"><?php esc_html_e( 'Search for:', 'the-alpha' ); ?></label>
			<input type="search" id="the-alpha-topbar-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search the blog…', 'the-alpha' ); ?>" required>
			<button type="submit" aria-label="<?php esc_attr_e( 'Submit search', 'the-alpha' ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M4 12h15m0 0-6-6m6 6-6 6"/></svg>
			</button>
		</form>
	</div>
</header>
<div class="scrim" data-close-nav hidden></div>

<div class="layout">
	<aside class="sidebar" id="sidebar">
		<div class="brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<span class="brand__rule"><span class="brand__name"><?php bloginfo( 'name' ); ?></span></span>
					<span class="brand__tag"><?php esc_html_e( 'aka — The Alpha', 'the-alpha' ); ?></span>
				</a>
			<?php endif; ?>

			<p class="brand__desc"><?php bloginfo( 'description' ); ?></p>

			<a class="brand__avatar" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> &mdash; <?php esc_attr_e( 'Home', 'the-alpha' ); ?>">
				<img
					src="<?php echo esc_url( add_query_arg( 'v', the_alpha_asset_ver( 'assets/img/avatar.webp' ), THE_ALPHA_URI . '/assets/img/avatar.webp' ) ); ?>"
					width="140" height="140" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					fetchpriority="high">
			</a>
		</div>

		<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'the-alpha' ); ?>">
			<?php the_alpha_primary_nav(); ?>
		</nav>

		<div class="sidebar__foot">
			<?php the_alpha_social_links( 'socials' ); ?>

			<button class="theme-toggle" type="button" aria-label="<?php esc_attr_e( 'Switch colour theme', 'the-alpha' ); ?>" aria-pressed="false">
				<svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
				<svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0-13a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V5a1 1 0 0 1 1-1zm0 15a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1zM4 12a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1zm14 0a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2h-1a1 1 0 0 1-1-1zM5.6 5.6a1 1 0 0 1 1.4 0l.7.7a1 1 0 1 1-1.4 1.4l-.7-.7a1 1 0 0 1 0-1.4zm10.7 10.7a1 1 0 0 1 1.4 0l.7.7a1 1 0 1 1-1.4 1.4l-.7-.7a1 1 0 0 1 0-1.4zM18.4 5.6a1 1 0 0 1 0 1.4l-.7.7a1 1 0 1 1-1.4-1.4l.7-.7a1 1 0 0 1 1.4 0zM7.7 16.3a1 1 0 0 1 0 1.4l-.7.7a1 1 0 0 1-1.4-1.4l.7-.7a1 1 0 0 1 1.4 0z"/></svg>
				<span class="theme-toggle__label">
					<span class="theme-toggle__label--to-light"><?php esc_html_e( 'Light', 'the-alpha' ); ?></span>
					<span class="theme-toggle__label--to-dark"><?php esc_html_e( 'Dark', 'the-alpha' ); ?></span>
				</span>
			</button>

			<p class="colophon-mini">
				<?php the_alpha_copyright( true ); ?><br>
				<?php esc_html_e( 'Built with', 'the-alpha' ); ?> <span class="colophon-mini__heart" role="img" aria-label="<?php esc_attr_e( 'love', 'the-alpha' ); ?>">&#10084;</span> <?php esc_html_e( 'and caffeine', 'the-alpha' ); ?>
			</p>
		</div>
	</aside>

	<main class="main" id="main">
