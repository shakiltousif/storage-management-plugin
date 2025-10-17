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

<div class="portal-account">
	<div class="account-section">
		<h2><?php esc_html_e( 'Profile Information', 'royal-storage' ); ?></h2>

		<form id="profile-form" class="account-form">
			<div class="form-group">
				<label for="display_name"><?php esc_html_e( 'Display Name', 'royal-storage' ); ?></label>
				<input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $customer_info->display_name ); ?>" required>
			</div>

			<div class="form-group">
				<label for="email"><?php esc_html_e( 'Email', 'royal-storage' ); ?></label>
				<input type="email" id="email" name="email" value="<?php echo esc_attr( $customer_info->email ); ?>" disabled>
			</div>

			<div class="form-group">
				<label for="phone"><?php esc_html_e( 'Phone', 'royal-storage' ); ?></label>
				<input type="tel" id="phone" name="phone" value="<?php echo esc_attr( $customer_info->phone ); ?>">
			</div>

			<div class="form-group">
				<label for="first_name"><?php esc_html_e( 'First Name', 'royal-storage' ); ?></label>
				<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $customer_info->first_name ); ?>">
			</div>

			<div class="form-group">
				<label for="last_name"><?php esc_html_e( 'Last Name', 'royal-storage' ); ?></label>
				<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $customer_info->last_name ); ?>">
			</div>

			<div class="form-group">
				<label for="address"><?php esc_html_e( 'Address', 'royal-storage' ); ?></label>
				<input type="text" id="address" name="address" value="<?php echo esc_attr( $customer_info->address ); ?>">
			</div>

			<div class="form-row">
				<div class="form-group">
					<label for="city"><?php esc_html_e( 'City', 'royal-storage' ); ?></label>
					<input type="text" id="city" name="city" value="<?php echo esc_attr( $customer_info->city ); ?>">
				</div>

				<div class="form-group">
					<label for="postcode"><?php esc_html_e( 'Postal Code', 'royal-storage' ); ?></label>
					<input type="text" id="postcode" name="postcode" value="<?php echo esc_attr( $customer_info->postcode ); ?>">
				</div>

				<div class="form-group">
					<label for="country"><?php esc_html_e( 'Country', 'royal-storage' ); ?></label>
					<input type="text" id="country" name="country" value="<?php echo esc_attr( $customer_info->country ); ?>">
				</div>
			</div>

			<button type="submit" class="btn btn-primary">
				<?php esc_html_e( 'Update Profile', 'royal-storage' ); ?>
			</button>
		</form>
	</div>

	<div class="account-section">
		<h2><?php esc_html_e( 'Change Password', 'royal-storage' ); ?></h2>

		<form id="password-form" class="account-form">
			<div class="form-group">
				<label for="current_password"><?php esc_html_e( 'Current Password', 'royal-storage' ); ?></label>
				<input type="password" id="current_password" name="current_password" required>
			</div>

			<div class="form-group">
				<label for="new_password"><?php esc_html_e( 'New Password', 'royal-storage' ); ?></label>
				<input type="password" id="new_password" name="new_password" required>
			</div>

			<div class="form-group">
				<label for="confirm_password"><?php esc_html_e( 'Confirm Password', 'royal-storage' ); ?></label>
				<input type="password" id="confirm_password" name="confirm_password" required>
			</div>

			<button type="submit" class="btn btn-primary">
				<?php esc_html_e( 'Change Password', 'royal-storage' ); ?>
			</button>
		</form>
	</div>
</div>

<style>
	.portal-account {
		padding: 20px;
	}

	.account-section {
		background: white;
		padding: 20px;
		border-radius: 8px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		margin-bottom: 20px;
	}

	.account-section h2 {
		margin-top: 0;
		margin-bottom: 20px;
		border-bottom: 2px solid #f0f0f0;
		padding-bottom: 10px;
	}

	.account-form {
		display: flex;
		flex-direction: column;
		gap: 15px;
	}

	.form-group {
		display: flex;
		flex-direction: column;
	}

	.form-group label {
		font-weight: bold;
		margin-bottom: 5px;
		color: #333;
	}

	.form-group input {
		padding: 10px;
		border: 1px solid #ddd;
		border-radius: 5px;
		font-size: 14px;
	}

	.form-group input:focus {
		outline: none;
		border-color: #667eea;
		box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
	}

	.form-group input:disabled {
		background: #f5f5f5;
		cursor: not-allowed;
	}

	.form-row {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
		gap: 15px;
	}

	.btn {
		padding: 10px 20px;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		font-weight: bold;
		display: inline-block;
		transition: all 0.3s ease;
		align-self: flex-start;
	}

	.btn-primary {
		background: #667eea;
		color: white;
	}

	.btn-primary:hover {
		background: #5568d3;
	}

	@media (max-width: 768px) {
		.form-row {
			grid-template-columns: 1fr;
		}
	}
</style>

