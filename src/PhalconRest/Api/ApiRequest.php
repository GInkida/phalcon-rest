<?php

namespace PhalconRest\Api;

use Phalcon\Validation;
use Phalcon\ValidationInterface;
use Phalcon\Validation\Validator;
use Phalcon\Http\Request;

class ApiRequest
{

    /**
     * @var Validation
     */
    protected $validator;

    public function __construct()
    {
        $this->validator = new Validation();

        $this->initialize();
    }

    /**
     * @return Validation
     */
    public function getValidator()
    {
        return $this->validator;
    }

    public function setValidator(ValidationInterface $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Setup rules in this method
     *
     * Like:
     *  $this->addRule('name', PresenceOf, 'Name is required')
     *
     */
    public function initialize()
    {

    }

    /**
     * @param $field
     * @param Validator $validator
     * @param null|string $message
     * @param null|string $description
     * @return $this
     */
    public function addRule($field, Validator $validator, $message = null, $description = null)
    {

        $class = get_class($validator);

        if (!$validator->hasOption('description')) {
            $validator->setOption('description', $description);
        }

        $this->validator->add($field, $validator);

        return $this;
    }

    /**
     * @param string $field
     * @param string|array $filters
     * @return $this
     */
    public function addFilter($field, $filters)
    {

        $this->validator->setFilters($field, $filters);

        return $this;
    }

    /**
     * @param null|string $field
     * @return mixed
     */
    public function getFilters($field = null)
    {
        return $this->validator->getFilters($field);
    }

    /**
     * @param $request array|Request
     * @return Validation\Message\Group
     */
    public function validate($request)
    {
        if ($request instanceof Request) {
            $request = $request->getPost();
        }

        return $this->validator->validate($request);
    }

    /**
     * Return array of validation rules:
     *
     * {
     *   fields: [
     *     {
     *       field: 'name',
     *       rules: [
     *         class: 'Phalcon\Validation\Validator\PresenceOf',
     *         message: null|message 'Should be presented'
     *         description: null|description 'Should be presented'
     *       ]
     *     }
     *   ]
     * }
     *
     * @return array
     */
    public function toArray()
    {
        $validators = $this->validator->getValidators();

        if (empty($validators)) {
            return null;
        }

        $fields = [];
        $count = 0;
        
        foreach ($validators as $field => $item) {
            foreach ($item as $validator) {
                $options = [
                    'message',
                    'min', // Validator\StringLength
                    'max', // Validator\StringLength
                    'messageMaximum', // Validator\StringLength
                    'messageMinimum', // Validator\StringLength
                    'format', // Validator\Date
                    'maxSize', // Validator\File
                    'messageSize', // Validator\File
                    'messageType', // Validator\File
                    'maxResolution', // Validator\File
                    'messageMaxResolution', // Validator\File
                    'accepted', // Validator\Identical
                    'domain', // Validator\ExclusionIn, Validator\InclusionIn
                    'pattern', // Validator\Regex
                    'minimum', // Validator\Between
                    'maximum', // Validator\Between
                    'with', // Validator\Confirmation
                ];

                $classes = [
                    "Phalcon\Validation\Validator\Alnum" => 'Альфанумеры',
                    "Phalcon\Validation\Validator\Alpha" => 'Алфавит',
                    "Phalcon\Validation\Validator\Date" => 'Валидная дата',
                    "Phalcon\Validation\Validator\Digit" => 'Цифры',
                    "Phalcon\Validation\Validator\File" => 'Файл',
                    "Phalcon\Validation\Validator\Uniqueness" => 'Уникальное',
                    "Phalcon\Validation\Validator\Numericality" => 'Номер',
                    "Phalcon\Validation\Validator\PresenceOf" => 'Не пусто',
                    "Phalcon\Validation\Validator\Identical" => 'Идентично другому',
                    "Phalcon\Validation\Validator\Email" => 'Почта',
                    "Phalcon\Validation\Validator\ExclusionIn" => 'Не входит в лист',
                    "Phalcon\Validation\Validator\InclusionIn" => 'Входит в лист',
                    "Phalcon\Validation\Validator\Regex	Validates" => 'Regex',
                    "Phalcon\Validation\Validator\StringLength" => 'Определенная длина строки',
                    "Phalcon\Validation\Validator\Between" => 'Между двумя значениями',
                    "Phalcon\Validation\Validator\Confirmation" => 'Подтверждение другого',
                    "Phalcon\Validation\Validator\Url" => 'Валидный URL',
                    "Phalcon\Validation\Validator\CreditCard" => 'Кредитная карта',
                    "Phalcon\Validation\Validator\Callback" => 'Функция',
                ];

                $extendedOptions = $validator->getOption('extendedOptions');

                if ($extendedOptions) {
                    $options = array_merge($options, (array)$extendedOptions);
                }

                $description = '';
                $message = '';

                foreach ($options as $option) {
                    if ($validator->hasOption($option)) {
                        $value = $validator->getOption($option);

                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }

                        $description .= $value;
                    }
                }

                if (method_exists($validator,'getValidators')) {
                    foreach ($validator->getValidators() as $childValidator) {
                        foreach ($options as $option) {
                            if ($childValidator->hasOption($option)) {
                                $value = $childValidator->getOption($option);

                                if (is_array($value)) {
                                    $value = implode(', ', $value);
                                }

                                $description .= $value;
                            }
                        }

                        if ($template = $childValidator->getTemplate()) {
                            $message = str_replace(':field', $field, $template);

                            foreach ($classes as $class => $value) {
                                $message = str_replace($class, $value, $message);
                            }
                        }
                    }
                }

                if ($template = $validator->getTemplate()) {
                    $message = str_replace(':field', $field, $template);

                    foreach ($classes as $class => $value) {
                        $message = str_replace($class, $value, $message);
                    }
                }

                if (isset($classes[get_class($validator)])) {
                    $class = $classes[get_class($validator)];
                }

                $fields[$count]['rules'][] = [
                    'class' => $class,
                    'message' => $message,
                    'description' => $description,
                ];
            }
            $fields[$count]['field'] = $field;
            $count++;
        }

        return [
            'fields' => $fields
        ];
    }
}