<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RecordSortName.php,v 1.1.2.5 2024/03/15 14:14:23 rtigero Exp $
namespace Pmb\DSI\Models\Sort\Entities\Record\RecordSortName;

use Pmb\DSI\Models\Sort\RootSort;

class RecordSortName extends RootSort
{

	protected $direction;

	public function __construct($data = null)
	{
		$this->type = static::TYPE_OTHER;
		if (isset($data->direction) && in_array($data->direction, static::DIRECTIONS)) {
			$this->direction = $data->direction;
		}
	}

	protected function sortData($records = array())
	{
		if ($this->direction == "ASC") {
			asort($records, SORT_STRING);
		} else {
			arsort($records, SORT_STRING);
		}
		return $records;
	}
}
