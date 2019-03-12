<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'o_#__csrf' => [
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'username' => $this->stringType(100)->notNull(),
					'date' => $this->text(),
				],
				'columns' => [
					'id' => $this->primaryKeyUnsigned(10),
					'username' => $this->stringType(100)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'referer' => $this->stringType(300)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__pdf' => [
				'columns' => [
					'pdfid' => $this->primaryKey(10)->unsigned(),
					'module_name' => $this->stringType(25)->notNull(),
					'header_content' => $this->text(),
					'body_content' => $this->text(),
					'footer_content' => $this->text(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'primary_name' => $this->stringType()->notNull(),
					'secondary_name' => $this->stringType()->notNull(),
					'meta_author' => $this->stringType()->notNull(),
					'meta_keywords' => $this->stringType()->notNull(),
					'metatags_status' => $this->smallInteger(1)->notNull(),
					'meta_subject' => $this->stringType()->notNull(),
					'meta_title' => $this->stringType()->notNull(),
					'page_format' => $this->stringType()->notNull(),
					'margin_chkbox' => $this->smallInteger(1),
					'margin_top' => $this->smallInteger(2)->unsigned(),
					'margin_bottom' => $this->smallInteger(2)->unsigned(),
					'margin_left' => $this->smallInteger(2)->unsigned(),
					'margin_right' => $this->smallInteger(2)->unsigned(),
					'header_height' => $this->smallInteger(2)->unsigned(),
					'footer_height' => $this->smallInteger(2)->unsigned(),
					'page_orientation' => $this->stringType(30)->notNull(),
					'language' => $this->stringType(7)->notNull(),
					'filename' => $this->stringType()->notNull(),
					'visibility' => $this->stringType(200)->notNull(),
					'default' => $this->smallInteger(1),
					'conditions' => $this->text(),
					'watermark_type' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'watermark_text' => $this->text()->notNull(),
					'watermark_angle' => $this->smallInteger(3)->unsigned()->notNull(),
					'watermark_image' => $this->stringType()->notNull(),
					'template_members' => $this->text()->notNull(),
					'one_pdf' => $this->smallInteger(1),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'metatags_status' => $this->tinyInteger(1)->notNull(),
					'margin_chkbox' => $this->tinyInteger(1),
					'default' => $this->tinyInteger(1),
					'watermark_type' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'one_pdf' => $this->tinyInteger(1),
				],
				'index' => [
					['module_name', ['module_name', 'status']],
					['module_name_2', 'module_name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
		];
	}
}
