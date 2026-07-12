<?php

namespace App\Services;

use App\Models\Country;
use App\Models\QuizQuestion;

class QuestionGeneratorService
{
    public function generateAllQuestions(): array
    {
        $countries = Country::findAll();
        $generated = 0;
        $errors = 0;

        QuizQuestion::deleteAll();

        foreach ($countries as $countryData) {
            $country = Country::findById($countryData['id']);
            if (!$country) {
                continue;
            }

            try {
                if ($country->getCapital() !== 'Unknown') {
                    $this->generateCapitalToCountryQuestions($country);
                    $this->generateCountryToCapitalQuestions($country);
                }

                if (!empty($country->getFlagUrl())) {
                    $this->generateFlagToCountryQuestions($country);
                    $this->generateCountryToFlagQuestions($country);
                }

                if ($country->getPopulation()) {
                    $this->generatePopulationQuestions($country);
                }

                if ($country->getArea()) {
                    $this->generateAreaQuestions($country);
                }

                $generated += 6;
            } catch (\Exception $e) {
                $errors++;
            }
        }

        return [
            'total' => $generated,
            'countries' => count($countries),
            'errors' => $errors
        ];
    }

    private function generateFlagToCountryQuestions(Country $country): void
    {
        $options = $this->getRandomCountries(4, $country->getId());
        $options[] = [
            'id' => $country->getId(),
            'name' => $country->getName()
        ];
        shuffle($options);

        $questionData = [
            'correct_answer' => $country->getName(),
            'options' => $options,
            'flag_url' => $country->getFlagUrl(),
            'question_text' => 'Какая страна изображена на флаге?'
        ];

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_FLAG_TO_COUNTRY,
            $questionData
        );
        $question->save();
    }

    private function generateCountryToFlagQuestions(Country $country): void
    {
        $options = $this->getRandomCountriesWithFlags(4, $country->getId());
        $options[] = [
            'id' => $country->getId(),
            'name' => $country->getName(),
            'flag_url' => $country->getFlagUrl()
        ];
        shuffle($options);

        $questionData = [
            'correct_answer' => $country->getName(),
            'options' => $options,
            'country_name' => $country->getName(),
            'correct_flag_url' => $country->getFlagUrl(),
            'question_text' => 'Какой флаг принадлежит стране ' . $country->getName() . '?'
        ];

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_COUNTRY_TO_FLAG,
            $questionData
        );
        $question->save();
    }

    private function generateCapitalToCountryQuestions(Country $country): void
    {
        $options = $this->getRandomCountries(4, $country->getId());
        $options[] = [
            'id' => $country->getId(),
            'name' => $country->getName()
        ];
        shuffle($options);

        $questionData = [
            'correct_answer' => $country->getName(),
            'options' => $options,
            'capital' => $country->getCapital(),
            'question_text' => 'Столица "' . $country->getCapital() . '" принадлежит какой стране?'
        ];

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_CAPITAL_TO_COUNTRY,
            $questionData
        );
        $question->save();
    }

    private function generateCountryToCapitalQuestions(Country $country): void
    {
        $options = $this->getRandomCapitals(4, $country->getId());
        $options[] = [
            'id' => $country->getId(),
            'capital' => $country->getCapital()
        ];
        shuffle($options);

        $questionData = [
            'correct_answer' => $country->getCapital(),
            'options' => $options,
            'country_name' => $country->getName(),
            'question_text' => 'Какая столица у страны ' . $country->getName() . '?'
        ];

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_COUNTRY_TO_CAPITAL,
            $questionData
        );
        $question->save();
    }

    private function generatePopulationQuestions(Country $country): void
    {
        if (!$country->getPopulation()) {
            return;
        }

        $options = $this->getRandomCountries(4, $country->getId());
        $options[] = [
            'id' => $country->getId(),
            'name' => $country->getName()
        ];
        shuffle($options);

        $questionData = [
            'correct_answer' => $country->getName(),
            'options' => $options,
            'population' => $country->getPopulation(),
            'question_text' => 'Какая страна имеет население ' . number_format($country->getPopulation()) . ' человек?'
        ];

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_POPULATION,
            $questionData
        );
        $question->save();
    }

    private function generateAreaQuestions(Country $country): void
    {
        if (!$country->getArea()) {
            return;
        }

        $options = $this->getRandomCountries(4, $country->getId());
        $options[] = [
            'id' => $country->getId(),
            'name' => $country->getName()
        ];
        shuffle($options);

        $questionData = [
            'correct_answer' => $country->getName(),
            'options' => $options,
            'area' => $country->getArea(),
            'question_text' => 'Какая страна имеет площадь ' . number_format($country->getArea()) . ' км²?'
        ];

        $question = new QuizQuestion(
            $country->getId(),
            QuizQuestion::TYPE_AREA,
            $questionData
        );
        $question->save();
    }

    private function getRandomCountries(int $count, int $excludeId): array
    {
        $countries = Country::getRandomCountries($count, [$excludeId]);
        $result = [];
        foreach ($countries as $country) {
            $result[] = [
                'id' => $country['id'],
                'name' => $country['name']
            ];
        }
        return $result;
    }

    private function getRandomCountriesWithFlags(int $count, int $excludeId): array
    {
        $countries = Country::getRandomCountries($count, [$excludeId]);
        $result = [];
        foreach ($countries as $country) {
            $result[] = [
                'id' => $country['id'],
                'name' => $country['name'],
                'flag_url' => $country['flag_url']
            ];
        }
        return $result;
    }

    private function getRandomCapitals(int $count, int $excludeId): array
    {
        $countries = Country::getRandomCountriesWithCapitals($count, $excludeId);
        $result = [];
        foreach ($countries as $country) {
            $result[] = [
                'id' => $country['id'],
                'capital' => $country['capital']
            ];
        }
        return $result;
    }
}
