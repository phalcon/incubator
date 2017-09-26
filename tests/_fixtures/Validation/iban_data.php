<?php

return [
    'iban-codes' => [
        [
            'TR',
            'TR330006100519786457841326',
        ],
        [
            'PT',
            'PT50000201231234567890154',
        ],
        [
            'AT',
            'AT611904300234573201',
        ],
        [
            'SE',
            'SE4550000000058398257466',
        ],
        [
            'CH',
            'CH9300762011623852957',
        ],
        [
            'GB',
            'GB29NWBK60161331926819',
        ],
        [
            'MT',
            'MT84MALT011000012345MTLCAST001S',
        ],
        [
            'MD',
            'MD24AG000225100013104168',
        ],
        [
            'HU',
            'HU42117730161111101800000000',
        ],
        [
            'GR',
            'GR1601101250000000012300695',
        ],
        [
            'DE',
            'DE89370400440532013000',
        ],
        [
            'EE',
            'EE382200221020145685',
        ],
    ],
    'iban-error-code' => [
        [
            'CY',
            '',
            'The input has a false IBAN format',
            'messageFalseFormat',
        ],
        [
            'CY',
            'CY170020012800000012005', //not enough symbols in code
            'The input has a false IBAN format',
            'messageFalseFormat',
        ],
        [
            'DE',
            'TR330006100519786457841326',
            'The input has a false IBAN format',
            'messageFalseFormat',
        ],
        [
            'ZZ',
            'TR330006100519786457841326',
            'Unknown country within the IBAN',
            'messageNotSupported',
        ],
        [
            'AD',
            'AD1200012030200359100100',
            'Countries outside the Single Euro Payments Area (SEPA) are not supported',
            'messageSepaNotSupported',
        ],
        [
            'AT',
            'AT611904300234573205', //changed last symbol. should be - AT611904300234573201
            'The input has failed the IBAN check',
            'messageCheckFailed',
        ],
    ],
];
