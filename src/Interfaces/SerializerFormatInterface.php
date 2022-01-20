<?php

namespace ApiClientBundle\Interfaces;

interface SerializerFormatInterface
{
    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';
    public const FORMAT_CSV = 'csv';
    public const FORMAT_YAML = 'yaml';
}
