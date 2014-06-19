<?php
namespace Phalcon\Validation\Validator\Upload;

/**
 * Validator Upload MimeType.
 * Validates that uploaded object of type \Phalcon\Http\Request\File has expected mime type value.
 * Custom options:
 *     - allowedMimeTypes => array of allowed mime types
 * <code>
 * foreach ($this->request->getUploadedFiles() as $file) {
 *     $validation = new \Phalcon\Validation();
 *     $validation->add('file', new \Phalcon\Validation\Validator\Upload\MimeType(
 *         array(
 *             'allowedMimeTypes' => array('image/jpeg', 'image/pjpeg', 'image/png'),
 *             'message' => 'Invalid mime type ":mimeType". Only files of type jpg and png are allowed.'
 *         )
 *     );
 * }
 * </code>
 *
 * @package Phalcon\Validation\Validator\Upload
 */
class MimeType extends \Phalcon\Validation\Validator
{
    /**
     * Executes the validation
     *
     * @param \Phalcon\Validator $validator validator
     * @param string             $attribute attribute
     *
     * @return \Phalcon\Validation\Message\Group
     */
    public function validate($validator, $attribute)
    {
        $isValid = true;
        $file = $validator->getValue($attribute);
        $allowedMimeTypes = $this->getOption('allowedMimeTypes');

        $this->checkSanity($file, $allowedMimeTypes);

        $message = $this->getMessage($validator);

        $label = $this->getLabel($validator, $attribute);

        if (!in_array($file->getRealType(), $allowedMimeTypes)) {
            $isValid = false;
            $validator->appendMessage(
                new \Phalcon\Validation\Message(
                    str_replace(array(':field', ':mimeType'), array($label, $file->getRealType()), $message),
                    $attribute,
                    'MimeType'
                )
            );
        }

        return $isValid;
    }

    /**
     * Checks custom options sanity.
     *
     * @param mixed $file             uploaded file
     * @param mixed $allowedMimeTypes allowed mime types
     *
     * @throws \UnexpectedValueException If options are not sane
     *
     * @return void
     */
    protected function checkSanity($file, $allowedMimeTypes)
    {
        // sanity checks
        if (!$file instanceof \Phalcon\Http\Request\File) {
            throw new \UnexpectedValueException('Option "file" must be instance of Phalcon\Http\Request\File.');
        }

        if (!$allowedMimeTypes || !is_array($allowedMimeTypes)) {
            throw new \UnexpectedValueException('"AllowedMimeTypes" option must be array with at least one value set.');
        }
    }

    /**
     * Gets message taking into account default message if one is not set.
     *
     * @param \Phalcon\Validation $validator validator
     *
     * @return string message
     */
    protected function getMessage(\Phalcon\Validation $validator)
    {
        // get message
        $message = $this->getOption('message');
        if (!$message) {
            $message = $validator->getDefaultMessage('MimeType');
        }
        return $message;
    }

    /**
     * Gets label.
     *
     * @param \Phalcon\Validation $validator validator
     * @param string              $attribute attribute
     *
     * @return mixed
     */
    protected function getLabel(\Phalcon\Validation $validator, $attribute)
    {
        $label = $this->getOption('label');
        if (!$label) {
            $label = $validator->getLabel($attribute);
        }
        if (!$label) {
            $label = $attribute;
        }
        return $label;
    }
}
