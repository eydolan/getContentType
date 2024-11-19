<?php
/*
 * GetContentTypeName
 * Usage: [[GetContentTypeName? &id=`1`]]
*/

// Get the content type ID from the parameter, defaulting to current resource's content type if not specified
$contentTypeId = $modx->getOption('id', $scriptProperties, $modx->resource->get('content_type'));

// Get the content type object
$contentType = $modx->getObject('modContentType', array('id' => $contentTypeId));

// Return the name if found, otherwise return empty or error message
if ($contentType) {
    return $contentType->get('name');
} else {
    return 'Content type not found';
}
