<?php


namespace YBooksManager\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model {
	protected $table = 'books_info';
	protected $primaryKey = 'id';
	public $timestamps = false;

	protected $fillable = [ 'post_id', 'isbn' ];

	/**
	 * Retrieve a book by post_id.
	 */
	public static function getByPostId( $postId ) {
		return self::where( 'post_id', $postId )->first();
	}

	/**
	 * Create a new book entry.
	 */
	public static function createBook( $postId, $isbn ) {
		return self::create( [
			'post_id' => $postId,
			'isbn'    => $isbn,
		] );
	}

	/**
	 * Update a book entry.
	 */
	public static function updateBook( $postId, $isbn ) {
		return self::where( 'post_id', $postId )->update( [ 'isbn' => $isbn ] );
	}

	/**
	 * Delete a book entry.
	 */
	public static function deleteBook( $postId ) {
		return self::where( 'post_id', $postId )->delete();
	}
}
