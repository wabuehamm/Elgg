<?php
/**
 * Translation file
 *
 * Note: don't change the return array to short notation because Transifex can't handle those during `tx push -s`
 */

return array(

	/**
	 * Menu items and titles
	 */

	'messageboard:board' => "伝言板",
	'messageboard:messageboard' => "伝言板",
	'messageboard:none' => "伝言はありません。",
	'messageboard:num_display' => "表示数",
	'messageboard:user' => "%sさんの伝言板",
	'messageboard:owner' => '%sさんの伝言板',
	'messageboard:owner_history' => '%sさんは、%sさんの伝言板に伝言を残しています',

	/**
	 * Message board widget river
	 */
	'river:user:messageboard' => "%s posted on %s's message board",

	/**
	 * Status messages
	 */

	'annotation:delete:messageboard:fail' => "Sorry, we could not delete this message",
	'annotation:delete:messageboard:success' => "You successfully deleted the message",
	
	'messageboard:posted' => "伝言を書き込みをしました。",
	'messageboard:deleted' => "伝言を削除しました。",

	/**
	 * Email messages
	 */

	'messageboard:email:subject' => '伝言板にコメントがされています！',
	'messageboard:email:body' => "You have a new message board comment from %s.

It reads:

%s

To view your message board comments, click here:
%s

To view %s's profile, click here:
%s",

	/**
	 * Error messages
	 */

	'messageboard:blank' => "申し訳ありません。メッセージ欄が空欄では保存できません。",
	'messageboard:notdeleted' => "申し訳ありません。書き込みを削除できません。",

	'messageboard:failure' => "書き込みの際に何からのエラーが発生しました。もう一度お試しください。",

	'widgets:messageboard:name' => "伝言板",
	'widgets:messageboard:description' => "「伝言板」を使うとプロフィールページ上でいろいろな人から書き込みをしてもらえます。",
);
