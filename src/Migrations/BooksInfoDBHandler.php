<?php


namespace YBooksManager\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class BooksInfoDBHandler {
	public static function up() {
		Capsule::schema()->create( 'books_info', function ( $table ) {
			$table->mediumIncrements( 'id' );
			$table->unsignedBigInteger( 'post_id' )->unique();
			$table->string( 'isbn', 13 );
			$table->timestamps();
		} );
	}

	public static function down() {
		Capsule::schema()->dropIfExists( 'books_info' );
	}
}
