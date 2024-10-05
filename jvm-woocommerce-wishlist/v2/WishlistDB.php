<?php
namespace CIXW_WISHLIST;

use CodesVault\Howdyqb\DB;

class WishlistDB {

	/**
	 * @var mixed
	 */
	protected $db;
	public static $table_name = 'cix_wishlist';


	/**
	 * Helper constructor.
	 */
	public function __construct() {

		$this->create_table();
	}
	/**
	 * Add table to the database on plugin activation.
	 * @return void
	 */
	public static function add_table() {
		// add create table code here
		( new self() )->create_table();
	}

	/**
	 * Creates the database table for the WishlistDB class.
	 *
	 * This method checks if the table already exists in the database. If not, it creates the table with the required columns.
	 *
	 * @return void
	 */
	protected function create_table(): void {
		$this->db = DB::setConnection();
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;

		// Check if the table exists
		$table = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;

		if ( ! $table ) {
			$this->db::create( self::$table_name )
				->column( 'ID' )->bigInt()->unsigned()->autoIncrement()->primary()->required()
				->column( 'user_id' )->bigInt()->unsigned()->required()
				->column( 'product_ids' )->longText()->required()
				->column( 'name' )->string( 255 )->required()
				->column( 'created_at' )->dateTime()
				->column( 'updated_at' )->dateTime()
				->index( array( 'ID' ) )
				->execute();
		}
		return;
	}
	public static function insert() {
		$db = DB::setConnection();

		$db::insert(
			self::$table_name,
			array(
				array(
					'user_id'     => 1,
					'product_ids' => serialize( Wishlist::wishlist_product_ids() ),
					'name'        => 'Test Wishlist',
					'created_at'  => current_time( 'mysql' ),
					'updated_at'  => current_time( 'mysql' ),
				),
			)
		);
	}
}
