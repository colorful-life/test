<?php
// https://ja.wordpress.org/support/topic/mw-wp-form%E3%81%AE%E8%87%AA%E5%8B%95%E8%BF%94%E4%BF%A1%E3%83%A1%E3%83%BC%E3%83%AB%E3%81%A7%E3%83%81%E3%82%A7%E3%83%83%E3%82%AF%E3%83%9C%E3%83%83%E3%82%AF%E3%82%B9%E3%81%A7%E8%A4%87%E6%95%B0%E9%81%B8/

class CLF_Mw_Form_Mail_Filter {
	protected static $instance = null;

	// セパレータを改行に置き換えたいフィールド名の配列
	protected $field_names = array(
		'チェックBOX_test1',
		'チェックBOX_test2',
	);

	private function __construct() {
		add_action( 'mwform_after_exec_shortcode', array($this, 'mwform_after_exec_shortcode') );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function mwform_after_exec_shortcode( $form_key ) {
		add_filter( 'mwform_admin_mail_' . $form_key, array( $this, 'mwform_mail_body_sep_to_lf' ), 10, 3 );
		add_filter( 'mwform_auto_mail_' . $form_key, array( $this, 'mwform_mail_body_sep_to_lf' ), 10, 3 );
	}

	public function mwform_mail_body_sep_to_lf( $Mail, $values, $Data ) {
		$fields = $this->field_names;
		// 送信メール本文
		$body = $Mail->body;
		foreach ( $fields as $field_name ) {
			$value = $Data->get($field_name);	// 送信される値（セパレーター区切りの選択項目文字列）
			if ( false === empty( $value ) ) {
				$pattern = '/' . preg_quote($value, '/') . '/';
				$separator = $Data->get_separator_value( $field_name );	// セパレーター
				// メール本文内の選択項目値のセパレーターを改行に置換
				$body = preg_replace_callback(
					$pattern,
					function( $match ) use ( $separator ) {
						$field_values = explode( $separator, $match[0] );
						return implode("\n", $field_values);
					},
					$body
				);
			}
		}
		$Mail->body = $body;
		return $Mail;
	}
}

CLF_Mw_Form_Mail_Filter::get_instance();
