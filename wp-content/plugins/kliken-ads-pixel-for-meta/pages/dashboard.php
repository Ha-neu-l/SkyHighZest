<?php
/**
 * Dashboard page.
 *
 * @package Kliken: Ads + Pixel for Meta
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wrap kk-wrapper">
	<div class="connected">
		<h2 class="connected-header flex align-center">
			<svg width="41" height="41" viewBox="0 0 41 41" fill="none" xmlns="http://www.w3.org/2000/svg">
				<g clip-path="url(#clip0_1000_17144)">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M40.7139 20.293C40.7139 9.24727 31.7596 0.292969 20.7139 0.292969C9.66816 0.292969 0.713867 9.24727 0.713867 20.293C0.713867 30.2755 8.02758 38.5496 17.5889 40.05V26.0742H12.5107V20.293H17.5889V15.8867C17.5889 10.8742 20.5747 8.10547 25.1432 8.10547C27.3313 8.10547 29.6201 8.49609 29.6201 8.49609V13.418H27.0982C24.6137 13.418 23.8389 14.9596 23.8389 16.5413V20.293H29.3857L28.499 26.0742H23.8389V40.05C33.4002 38.5496 40.7139 30.2755 40.7139 20.293Z" fill="#0866FF"/>
				<path fill-rule="evenodd" clip-rule="evenodd" d="M29.3857 20.293H23.8389V16.5413C23.8389 14.9596 24.6137 13.418 27.0982 13.418H29.6201V8.49609C29.6201 8.49609 27.3313 8.10547 25.1432 8.10547C20.5747 8.10547 17.5889 10.8742 17.5889 15.8867V20.293H12.5107V26.0742H17.5889V40.05C18.6071 40.2098 19.6507 40.293 20.7139 40.293C21.777 40.293 22.8206 40.2098 23.8389 40.05V26.0742H28.499L29.3857 20.293Z" fill="white"/>
				</g>
				<defs>
				<clipPath id="clip0_1000_17144">
				<rect width="40" height="40" fill="white" transform="translate(0.713867 0.292969)"/>
				</clipPath>
				</defs>
			</svg>
			<?php
				/* translators: "Facebook Ads" is product name. Do not translate. */
				esc_html_e( 'Facebook Ads campaigns', 'kliken-ads-pixel-for-meta' );
			?>
		</h2>
		<div class="connected-cards">
			<div class="container">
				<a class="flex" href="<?php echo esc_url( KK_FB_WC_WOOKLIKEN_BASE_URL . 'meta/default.aspx?goto=shopnew&acct=' . intval( $settings['account_id'] ) ); ?>">
					<span class="connected-icon">
						<svg width="49" height="49" viewBox="0 0 49 49" fill="none" xmlns="http://www.w3.org/2000/svg">
							<mask id="mask0_913_34414" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="49" height="49">
							<rect x="0.854004" y="0.457031" width="48" height="48" fill="#D9D9D9"/>
							</mask>
							<g mask="url(#mask0_913_34414)">
							<path d="M10.7387 41.4569C9.95435 41.4569 9.2775 41.1722 8.7081 40.6028C8.1387 40.0334 7.854 39.3566 7.854 38.5723V10.3417C7.854 9.55738 8.1387 8.88053 8.7081 8.31113C9.2775 7.74173 9.95435 7.45703 10.7387 7.45703H23.9308V9.72623H10.7387C10.5848 9.72623 10.4437 9.79033 10.3155 9.91853C10.1873 10.0468 10.1232 10.1878 10.1232 10.3417V38.5723C10.1232 38.7262 10.1873 38.8672 10.3155 38.9954C10.4437 39.1236 10.5848 39.1877 10.7387 39.1877H38.9693C39.1231 39.1877 39.2642 39.1236 39.3924 38.9954C39.5206 38.8672 39.5847 38.7262 39.5847 38.5723V25.3801H41.8539V38.5723C41.8539 39.3566 41.5692 40.0334 40.9998 40.6028C40.4304 41.1722 39.7536 41.4569 38.9693 41.4569H10.7387ZM33.5847 21.7262V15.7262H27.5847V13.457H33.5847V7.45703H35.8539V13.457H41.8539V15.7262H35.8539V21.7262H33.5847Z"/>
							</g>
						</svg>
					</span>
					<span class="connected-content flex">
						<span class="connected-heading">
							<?php esc_html_e( 'Create new ad', 'kliken-ads-pixel-for-meta' ); ?>
						</span>
						<span class="connected-text">
							<?php
								/* translators: Facebook, Instagram, and Advantage+ are product names. Do not translate. */
								esc_html_e( 'Launch new ad campaigns across Facebook and Instagram with Advantage+ for maximum reach and efficiency.', 'kliken-ads-pixel-for-meta' );
							?>
						</span>
					</span>
				</a>
			</div>

			<div class="container">
				<a class="flex" href="<?php echo esc_url( KK_FB_WC_WOOKLIKEN_BASE_URL . 'meta/default.aspx?goto=shopmanage&acct=' . intval( $settings['account_id'] ) ); ?>">
					<span class="connected-icon">
						<svg width="49" height="49" viewBox="0 0 49 49" fill="none" xmlns="http://www.w3.org/2000/svg">
							<mask id="mask0_913_34417" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="49" height="49">
							<rect x="0.713867" y="0.457031" width="48" height="48" fill="#D9D9D9"/>
							</mask>
							<g mask="url(#mask0_913_34417)">
							<path d="M10.5985 41.4571C9.81422 41.4571 9.13737 41.1724 8.56797 40.603C7.99857 40.0336 7.71387 39.3567 7.71387 38.5724V10.3418C7.71387 9.5575 7.99857 8.88066 8.56797 8.31126C9.13737 7.74186 9.81422 7.45715 10.5985 7.45715H29.6561L27.3869 9.72636H10.5985C10.4447 9.72636 10.3036 9.79046 10.1754 9.91866C10.0472 10.0469 9.98307 10.1879 9.98307 10.3418V38.5724C9.98307 38.7263 10.0472 38.8673 10.1754 38.9956C10.3036 39.1238 10.4447 39.1879 10.5985 39.1879H38.8291C38.983 39.1879 39.124 39.1238 39.2523 38.9956C39.3805 38.8673 39.4446 38.7263 39.4446 38.5724V21.5763L41.7138 19.3071V38.5724C41.7138 39.3567 41.4291 40.0336 40.8597 40.603C40.2903 41.1724 39.6134 41.4571 38.8291 41.4571H10.5985ZM19.7139 29.4571V22.5918L38.3908 3.91486C38.6138 3.69179 38.8638 3.53282 39.1408 3.43795C39.4177 3.34309 39.701 3.29565 39.9908 3.29565C40.2677 3.29565 40.5474 3.34822 40.8301 3.45335C41.1127 3.55849 41.3701 3.72259 41.6023 3.94565L45.0984 7.43405C45.334 7.66852 45.5109 7.92901 45.6289 8.21551C45.747 8.50197 45.806 8.79205 45.806 9.08575C45.806 9.37945 45.7521 9.67067 45.6442 9.95941C45.5363 10.2481 45.3672 10.5089 45.1368 10.7417L26.483 29.4571H19.7139ZM21.9831 27.1879H25.5407L38.7677 13.9609L36.9831 12.1879L35.1216 10.3956L21.9831 23.5033V27.1879Z"/>
							</g>
						</svg>
					</span>
					<span class="connected-content flex">
						<span class="connected-heading">
							<?php esc_html_e( 'Manage ads', 'kliken-ads-pixel-for-meta' ); ?>
						</span>
						<span class="connected-text">
							<?php esc_html_e( 'Easily view and adjust your existing ads to optimize performance and maximize ROI.', 'kliken-ads-pixel-for-meta' ); ?>
						</span>
					</span>
				</a>
			</div>

			<div class="container">
				<a class="flex" href="<?php echo esc_url( KK_FB_WC_WOOKLIKEN_BASE_URL . 'meta/default.aspx?goto=account&acct=' . intval( $settings['account_id'] ) ); ?>">
					<span class="connected-icon">
						<svg width="49" height="49" viewBox="0 0 49 49" fill="none" xmlns="http://www.w3.org/2000/svg">
							<mask id="mask0_913_34420" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="49" height="49">
							<rect x="0.713867" y="0.457031" width="48" height="48" fill="#D9D9D9"/>
							</mask>
							<g mask="url(#mask0_913_34420)">
							<path d="M15.587 34.0685H17.8561V21.2416H15.587V34.0685ZM23.5792 34.0685H25.8484V14.8455H23.5792V34.0685ZM31.5715 34.0685H33.8407V27.4955H31.5715V34.0685ZM10.5985 41.4569C9.81422 41.4569 9.13737 41.1722 8.56797 40.6028C7.99857 40.0334 7.71387 39.3566 7.71387 38.5723V10.3417C7.71387 9.55738 7.99857 8.88053 8.56797 8.31113C9.13737 7.74173 9.81422 7.45703 10.5985 7.45703H38.8291C39.6134 7.45703 40.2903 7.74173 40.8597 8.31113C41.4291 8.88053 41.7138 9.55738 41.7138 10.3417V38.5723C41.7138 39.3566 41.4291 40.0334 40.8597 40.6028C40.2903 41.1722 39.6134 41.4569 38.8291 41.4569H10.5985ZM10.5985 39.1877H38.8291C38.983 39.1877 39.124 39.1236 39.2523 38.9954C39.3805 38.8672 39.4446 38.7262 39.4446 38.5723V10.3417C39.4446 10.1878 39.3805 10.0468 39.2523 9.91853C39.124 9.79033 38.983 9.72623 38.8291 9.72623H10.5985C10.4447 9.72623 10.3036 9.79033 10.1754 9.91853C10.0472 10.0468 9.98307 10.1878 9.98307 10.3417V38.5723C9.98307 38.7262 10.0472 38.8672 10.1754 38.9954C10.3036 39.1236 10.4447 39.1877 10.5985 39.1877Z"/>
							</g>
						</svg>
					</span>
					<span class="connected-content flex">
						<span class="connected-heading">
							<?php esc_html_e( 'Manage account', 'kliken-ads-pixel-for-meta' ); ?>
						</span>
						<span class="connected-text">
							<?php esc_html_e( 'Update your account settings, billing information, and preferences to keep everything running smoothly.', 'kliken-ads-pixel-for-meta' ); ?>
						</span>
					</span>
				</a>
			</div>
		</div>
	</div>

	<details class="primer advanced-settings">
		<summary>
			<?php esc_html_e( 'Advanced Settings', 'kliken-ads-pixel-for-meta' ); ?>
		</summary>
		<!-- Settings form -->
		<div class="container settings">
			<div class="settings-header flex align-center">
				<h2><?php esc_html_e( 'Settings', 'kliken-ads-pixel-for-meta' ); ?></h2>
				<p>
					<a href="https://intercom.help/kliken/articles/5989948" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'What are these options?', 'kliken-ads-pixel-for-meta' ); ?>
					</a>
				</p>
			</div>

			<form action="<?php menu_page_url( KK_FB_WC_MAIN_PAGE_SLUG ); ?>" method="post" id="kk-settings-form" class="settings-form">
				<?php wp_nonce_field( KK_FB_WC_ACTION_SAVE_SETTINGS ); ?>
				<fieldset>
					<div class="flex align-center">
						<label for="account_id"><?php esc_html_e( 'Account Id', 'kliken-ads-pixel-for-meta' ); ?></label>
						<input type="text" name="account_id" id="account_id" disabled
								value="<?php echo esc_attr( $settings['account_id'] ); ?>" />
					</div>

					<div class="flex align-center">
						<label for="app_token"><?php esc_html_e( 'Application Token', 'kliken-ads-pixel-for-meta' ); ?></label>
						<input type="text" name="app_token" id="app_token" disabled
								value="<?php echo esc_attr( $settings['app_token'] ?? '' ); ?>" />
					</div>

					<div class="flex align-center fb-token">
						<label for="facebook_token">
							<?php
								/* translators: Facebook is product name. Do not translate. */
								esc_html_e( 'Facebook Verification Token', 'kliken-ads-pixel-for-meta' );
							?>
						</label>
						<input type="text" name="facebook_token" id="facebook_token" disabled
								value="<?php echo esc_attr( $settings['facebook_token'] ?? '' ); ?>" />
					</div>
				</fieldset>

				<button type="button" name="edit" id="edit-kk-settings" class="edit-settings edit">
					<?php esc_html_e( 'Edit Settings', 'kliken-ads-pixel-for-meta' ); ?>
				</button>

				<button type="submit" name="save" id="save-kk-settings" class="edit-settings save">
					<span class="save-text">
						<?php esc_html_e( 'Save Changes', 'kliken-ads-pixel-for-meta' ); ?>
					</span>
					<svg width="41" height="40" viewBox="0 0 41 40" fill="none" xmlns="http://www.w3.org/2000/svg">
						<mask id="mask0_1182_3077" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="41" height="40">
						<rect x="0.5" width="40" height="40" fill="white"/>
						</mask>
						<g mask="url(#mask0_1182_3077)">
						<path d="M20.5 36.6667C18.2223 36.6667 16.0695 36.2292 14.0417 35.3542C12.0139 34.4792 10.2431 33.2848 8.72921 31.7709C7.21532 30.257 6.02087 28.4862 5.14587 26.4584C4.27087 24.4306 3.83337 22.2778 3.83337 20C3.83337 17.6945 4.27087 15.5348 5.14587 13.5209C6.02087 11.507 7.21532 9.7431 8.72921 8.22921C10.2431 6.71532 12.0139 5.52087 14.0417 4.64587C16.0695 3.77087 18.2223 3.33337 20.5 3.33337C20.9723 3.33337 21.3681 3.4931 21.6875 3.81254C22.007 4.13199 22.1667 4.52782 22.1667 5.00004C22.1667 5.47226 22.007 5.8681 21.6875 6.18754C21.3681 6.50698 20.9723 6.66671 20.5 6.66671C16.8056 6.66671 13.6598 7.96532 11.0625 10.5625C8.46532 13.1598 7.16671 16.3056 7.16671 20C7.16671 23.6945 8.46532 26.8403 11.0625 29.4375C13.6598 32.0348 16.8056 33.3334 20.5 33.3334C24.1945 33.3334 27.3403 32.0348 29.9375 29.4375C32.5348 26.8403 33.8334 23.6945 33.8334 20C33.8334 19.5278 33.9931 19.132 34.3125 18.8125C34.632 18.4931 35.0278 18.3334 35.5 18.3334C35.9723 18.3334 36.3681 18.4931 36.6875 18.8125C37.007 19.132 37.1667 19.5278 37.1667 20C37.1667 22.2778 36.7292 24.4306 35.8542 26.4584C34.9792 28.4862 33.7848 30.257 32.2709 31.7709C30.757 33.2848 28.9931 34.4792 26.9792 35.3542C24.9653 36.2292 22.8056 36.6667 20.5 36.6667Z" fill="white"/>
						</g>
					</svg>
				</button>

				<button type="reset" id="cancel-edit-kk-settings" class="cancel">Cancel</button>
			</form>

			<?php if ( intval( $settings['account_id'] ) > 0 && ! empty( $settings['app_token'] ) ) : ?>
				<a href="<?php echo esc_url( \Kliken\FbWcPlugin\Helper::build_authorization_url( $settings['account_id'], $settings['app_token'] ) ); ?>">
					<button type="button" id="authorize-api-access" class="primary-button">
						<?php esc_html_e( 'Authorize API Access', 'kliken-ads-pixel-for-meta' ); ?>
					</button>
				</a>
			<?php endif; ?>
		</div>
	</details>
</div>
