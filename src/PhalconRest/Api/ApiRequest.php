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
        $rules = [];

        foreach ($this->validator->getValidators() as $item) {
            $field = $item[0];

            $validator = $item[1];

            if (!($label = $this->validator->getLabel($field))) {
                $label = $field;
            }

            if (!array_key_exists($field, $rules)) {
                $rules[$field] = [
                    'field' => $field,
                ];
            }

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

            $extendedOptions = $validator->getOption('extendedOptions');

            if ($extendedOptions) {
                $options = array_merge($options, (array)$extendedOptions);
            }

            $replaces = [
                ':field' => $label,
            ];

            foreach ($options as $option) {
                if ($validator->hasOption($option)) {
                    $value = $validator->getOption($option);

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $replaces[':' . $option] = $value;
                }
            }

            $message = $validator->getOption('message');

            if ($message === null) {
                $typeClass = get_class($validator);

                $type = substr($typeClass, strrpos($typeClass, '\\') + 1);

                $message = $this->validator->getDefaultMessage($type);
            }

            $description = $validator->getOption('description');

            if ($description === null) {
                $description = $message;
            }

            if ($description !== null) {
                $description = strtr($description, $replaces);
            }

            $rules[$field]['rules'][] = [
                'class' => get_class($validator),
                'message' => $message,
                'description' => $description,
            ];
        }

        $rules = array_values($rules);

        return [
            'fields' => $rules
        ];
    }
}