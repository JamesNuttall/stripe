<?php

namespace enupal\stripe\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\models\FieldGroup;
use Craft;

/**
 * m200707_000000_add_field_group_id migration.
 */
class m200707_000000_add_field_group_id extends Migration
{
    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $fieldGroupId = null;

        $fieldGroup = (new \yii\db\Query())
            ->select('*')
            ->from(['{{%fieldgroups}}'])
            ->where(['name' => 'Stripe Payments'])
            ->one();

        if (!isset($fieldGroup['id'])) {
            $fieldGroup = new FieldGroup();
            $fieldGroup->name = "Stripe Payments";
            Craft::$app->getFields()->saveGroup($fieldGroup);

            $fieldGroupId = $fieldGroup->id;
        } else {
            $fieldGroupId = $fieldGroup['id'];
        }

        $stripeFields = (new Query())
            ->select(['*'])
            ->from(["{{%fields}}"])
            ->where(['like', 'context', 'enupalStripe:'])
            ->all();

        if ($stripeFields && $fieldGroupId) {
            foreach ($stripeFields as $stripeField) {
                $matrixBlockTypes = (new Query())
                    ->select(['*'])
                    ->from(["{{%matrixblocktypes}}"])
                    ->where(['fieldId' => $stripeField['id']])
                    ->all();

                foreach ($matrixBlockTypes as $matrixBlockType) {
                    $this->update("{{%fields}}", [
                        'groupId' =>$fieldGroupId
                    ], [
                        'context' => 'matrixBlockType:'.$matrixBlockType['uid']
                    ], [], false);
                }

                if ($stripeField['groupId'] === null) {
                    $this->update("{{%fields}}", [
                        'groupId' =>$fieldGroupId
                    ], [
                        'id' => $stripeField['id']
                    ], [], false);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200707_000000_add_field_group_id cannot be reverted.\n";

        return false;
    }
}
