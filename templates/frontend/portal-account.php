<?php
/**
 * Portal Account Template
 *
 * @package RoyalStorage
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$account = new \RoyalStorage\Frontend\Account();
$customer_info = $account->get_customer_data( $current_user->ID );
?>

<div class="portal-account" style="padding: 2.5rem;">
	<div class="account-section" style="margin-bottom: 3rem;">
		<h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
			👤 <?php esc_html_e( 'Profile Information', 'royal-storage' ); ?>
		</h2>

		<form id="profile-form" class="account-form" style="display: grid; gap: 1.5rem;">
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
				<div class="royal-storage-form-group">
					<label for="display_name"><?php esc_html_e( 'Display Name', 'royal-storage' ); ?></label>
					<input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $customer_info->display_name ); ?>" required>
				</div>

				<div class="royal-storage-form-group">
					<label for="email"><?php esc_html_e( 'Email', 'royal-storage' ); ?></label>
					<input type="email" id="email" name="email" value="<?php echo esc_attr( $customer_info->email ); ?>" disabled style="background: var(--rs-bg-main); cursor: not-allowed;">
				</div>
			</div>

			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
				<div class="royal-storage-form-group">
					<label for="phone"><?php esc_html_e( 'Phone', 'royal-storage' ); ?></label>
					<input type="tel" id="phone" name="phone" value="<?php echo esc_attr( $customer_info->phone ); ?>">
				</div>

				<div class="royal-storage-form-group">
					<label for="first_name"><?php esc_html_e( 'First Name', 'royal-storage' ); ?></label>
					<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $customer_info->first_name ); ?>">
				</div>

				<div class="royal-storage-form-group">
					<label for="last_name"><?php esc_html_e( 'Last Name', 'royal-storage' ); ?></label>
					<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $customer_info->last_name ); ?>">
				</div>
			</div>

			<div class="royal-storage-form-group">
				<label for="address"><?php esc_html_e( 'Address', 'royal-storage' ); ?></label>
				<input type="text" id="address" name="address" value="<?php echo esc_attr( $customer_info->address ); ?>">
			</div>

			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">
				<div class="royal-storage-form-group">
					<label for="city"><?php esc_html_e( 'City', 'royal-storage' ); ?></label>
					<input type="text" id="city" name="city" value="<?php echo esc_attr( $customer_info->city ); ?>">
				</div>

				<div class="royal-storage-form-group">
					<label for="postcode"><?php esc_html_e( 'Postal Code', 'royal-storage' ); ?></label>
					<input type="text" id="postcode" name="postcode" value="<?php echo esc_attr( $customer_info->postcode ); ?>">
				</div>

				<div class="royal-storage-form-group">
					<label for="country"><?php esc_html_e( 'Country', 'royal-storage' ); ?></label>
					<input type="text" id="country" name="country" value="<?php echo esc_attr( $customer_info->country ); ?>">
				</div>
			</div>

			<button type="submit" class="royal-storage-btn" style="width: auto; padding: 0.75rem 2rem;">
				<?php esc_html_e( 'Update Profile', 'royal-storage' ); ?>
			</button>
		</form>
	</div>

	<div class="account-section" style="padding-top: 3rem; border-top: 1px solid var(--rs-border);">
		<h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
			🔒 <?php esc_html_e( 'Change Password', 'royal-storage' ); ?>
		</h2>

		<form id="password-form" class="account-form" style="display: grid; gap: 1.5rem; max-width: 500px;">
			<div class="royal-storage-form-group">
				<label for="current_password"><?php esc_html_e( 'Current Password', 'royal-storage' ); ?></label>
				<input type="password" id="current_password" name="current_password" required>
			</div>

			<div class="royal-storage-form-group">
				<label for="new_password"><?php esc_html_e( 'New Password', 'royal-storage' ); ?></label>
				<input type="password" id="new_password" name="new_password" required>
			</div>

			<div class="royal-storage-form-group">
				<label for="confirm_password"><?php esc_html_e( 'Confirm Password', 'royal-storage' ); ?></label>
				<input type="password" id="confirm_password" name="confirm_password" required>
			</div>

			<button type="submit" class="royal-storage-btn royal-storage-btn-secondary" style="width: auto; padding: 0.75rem 2rem;">
				<?php esc_html_e( 'Update Password', 'royal-storage' ); ?>
			</button>
		</form>
	</div>
</div>
