<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for the deployment_push_settings table
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @category   Models
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Model_Deployment_Push_Setting extends ORM {
	
	/**
	 * Membership definition
	 * @var array
	 */
	protected $_belongs_to = array(
	    'bucket' => array(),
	    'deployment' => array()
	);
	
	/**
	 * Validation rules for this model
	 * @return array
	 */
	public function rules()
	{
		return array(
			'push_drop_count' => array(
				array('not_empty'),
				array('digit')
			),
		);
	}
	
	/**
	 * Returns the settings for the specified bucket
	 *
	 * @param  int $bucket_id  ID of the bucket
	 * @return Model_Deployment_Setting
	 */
	public static function get_settings($bucket_id)
	{
		return ORM::factory('deployment_push_setting')
		    ->where('bucket_id','=', $bucket_id)
		    ->find();
	}
	
}