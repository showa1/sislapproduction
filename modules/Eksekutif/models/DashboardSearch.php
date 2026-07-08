<?php

namespace app\modules\eksekutif\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DashboardSearch represents the model behind the search form of the dashboard.
 */
class DashboardSearch extends TransaksiLayanan
{
    public $date_from;
    public $date_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to'], 'safe'],
            [['date_from', 'date_to'], 'default', 'value' => null],
        ];
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TransaksiLayanan::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Apply date range filters
        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'tanggal', $this->date_from]);
        }
        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'tanggal', $this->date_to]);
        }

        return $dataProvider;
    }
}
