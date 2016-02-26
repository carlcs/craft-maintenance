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
        $rules[] = array('startDate', 'validateStartDate');
        $rules[] = array('endDate', 'validateEndDate');

        return $rules;
    }

    /**
     * Adds validation rules to the startDate attribute.
     *
     * @return null
     */
    public function validateStartDate($attribute)
    {
        // Checks if they only entered an endDate, or if startDate is missing
        // but a maintenance mode was enabled
        if ((!$this->startDate && $this->endDate) || (($this->blockCp || $this->blockSite) && !$this->startDate)) {
            $message = Craft::t('Start Date cannot be blank .');
            $this->addError($attribute, $message);
        }
    }

    /**
     * Adds validation rules to the endDate attribute.
     *
     * @return null
     */
    public function validateEndDate($attribute)
    {
        if ($this->startDate && $this->endDate) {
            $startDate = $this->startDate->getTimestamp();
            $endDate   = $this->endDate->getTimestamp();

            if ($startDate > $endDate) {
                $message = Craft::t('End Date is less than Start Date.');
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
            'blockCp'   => array(AttributeType::Bool, 'default' => false),
            'blockSite' => array(AttributeType::Bool, 'default' => false),
            'sortOrder' => array(AttributeType::SortOrder),
        );
    }
}
