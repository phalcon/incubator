<?php
namespace Phalcon\Validation\Validator\Upload;

/**
 * Validator Upload FileSize.
 * Validates that object of Phalcon\Http\Request\File has expected min/max file size in bytes.
 * Custom options:
 *     - min => optional, minimal allowed file size in bytes
 *     - max => optional, maximal allowed file size in bytes
 *     - minMessage => optional,
 *                         message which will be used instead of "message" option when file size is less then min value
 *     - maxMessage => optional,
 *                         message which will be used instead of "message" option when file size is over max value
 *
 * <code>
 * foreach ($this->request->getUploadedFiles() as $file) {
 *     $validation = new \Phalcon\Validation();
 *     $validation->add('file', new \Phalcon\Validation\Validator\Upload\FileSize(
 *          array(
 *              'min' => 1024,
 *              'minMessage' => 'File ":field" must have size greater then 1024 bytes. File size is :fileSize bytes.',
 *              'max' => 10 * (1024 * 1024),
 *              'maxMessage' => 'File ":field" must have size less then 10 MB. File size is :fileSize bytes.',
 *          )
 *     );
 *     $messages = $validation->validate(array(
 *         'file' => $file,
 *     ));
 * }
 * </code>
 *
 * @package Phalcon\Validation\Validator\Upload
 */
class FileSize extends \Phalcon\Validation\Validator
{
    /**
     * Validates uploaded file according to "min" and "max" options.
     *
     * @param \Phalcon\Validator $validator validator
     * @param string             $attribute attribute
     *
     * @return \Phalcon\Validation\Message\Group
     */
    public function validate($validator, $attribute)
    {
        $isValid = true;

        $min = $this->getOption('min');
        $max = $this->getOption('max');
        $file = $validator->getValue($attribute);

        // sanity check
        $this->checkSanity($file, $min, $max);

        $message = $this->getMessage($validator);

        $label = $this->getLabel($validator, $attribute);

        // validate min
        if ($min) {
            if ($file->getSize() < (int) $min) {
                $isValid = false;
                $minMessage = $this->getOption('minMessage');
                if (!$minMessage) {
                    $minMessage = $message;
                }
                $validator->appendMessage(
                    new \Phalcon\Validation\Message(
                        str_replace(array(':field', ':fileSize'), array($label, $file->getSize()), $minMessage),
                        $attribute,
                        'FileSize'
                    )
                );
                return $isValid;
            }
        }

        // validate max
        if ($max) {
            if ($file->getSize() > (int) $max) {
                $isValid = false;
                $maxMessage = $this->getOption('maxMessage');
                if (!$maxMessage) {
                    $maxMessage = $message;
                }
                $validator->appendMessage(
                    new \Phalcon\Validation\Message(
                        str_replace(array(':field', ':fileSize'), array($label, $file->getSize()), $maxMessage),
                        $attribute,
                        'FileSize'
                    )
                );
                return $isValid;
            }
        }

        return $isValid;
    }

    /**
     * Checks custom options sanity.
     *
     * @param mixed|\Phalcon\Http\Request $file uploaded file
     * @param null|int                    $min  minimum size in bytes
     * @param null|int                    $max  maximum size in bytes
     *
     * @throws \UnexpectedValueException if options are not sane
     *
     * @return void
     */
    protected function checkSanity($file, $min = null, $max = null)
    {
        // sanity checks
        if (!$file instanceof \Phalcon\Http\Request\File) {
            throw new \UnexpectedValueException('Option "file" must be instance of Phalcon\Http\Request\File.');
        }

        if (!$min && !$max) {
            throw new \UnexpectedValueException('At least one of "min" or "max" options must be set.');
        }

        if ($min !== null && (int) $min <= 0) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Option "min" cannot be zero or negative number. %d given.',
                    $min
                )
            );
        }

        if ($max !== null && (int) $max <= 0) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Option "max" cannot be zero or negative number. %d given.',
                    $max
                )
            );
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
            $message = $validator->getDefaultMessage('FileSize');
        }
        return $message;
    }


    /**
     * Gets appropriate label.
     *
     * @param \Phalcon\Validation $validator validator
     * @param string              $attribute attribute
     *
     * @return string label
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
