<?php
namespace Craft;

class Maintenance_AnnouncementRecord extends BaseRecord
{
    /**
	 * Returns the name of the associated database table.
	 *
	 * @return string
	 */
    public function getTableName()
    {
        return 'maintenance_announcements';
    }

    /**
	 * Returns this model's validation rules.
	 *
	 * @return array
	 */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['endDate', 'validateEndDate'];

        return $rules;
    }

    /**
	 * Adds validation rules to the endDate attribute.
	 *
	 * @return null
	 */
    public function validateEndDate($attribute)
    {
        if ($this->endDate) {
            $startDate = $this->startDate->getTimestamp();
            $endDate   = $this->endDate->getTimestamp();

            if ($startDate > $endDate) {
                $message = Craft::t('Start date is greater than end date.');
                $this->addError($attribute, $message);
            }
        }
    }

    // Protected Methods
	// =========================================================================

    /**
	 * Defines this model's attributes.
	 *
	 * @return array
	 */
    protected function defineAttributes()
    {
        return array(
            'message'   => array(AttributeType::String, 'column' => ColumnType::Text, 'label' => Craft::t('Message'), 'required' => true),
            'startDate' => array(AttributeType::DateTime, 'label' => Craft::t('Start')),
            'endDate'   => array(AttributeType::DateTime, 'label' => Craft::t('End')),
            'blockCp'   => array(AttributeType::Bool, 'default' => true),
            'blockSite' => array(AttributeType::Bool, 'default' => false),
            'sortOrder' => array(AttributeType::SortOrder),
        );
    }
}
