<?php
/*
 * GetContentTypes
 * A snippet that returns all MODX content types
 * 
 * PARAMETERS:
 * &format - Output format (array|json) [default=array]
 * &tpl - Name of chunk for individual content type template [optional]
 * &wrapperTpl - Name of chunk for wrapping all results [optional]
 * 
 * USAGE EXAMPLES:
 * 1. Basic array output:
 *    [[GetContentTypes]]
 *  [[GetContentTypes? &tpl=`contentTypeRowTpl` &wrapperTpl=`contentTypeWrapperTpl`]]
 * 2. JSON output:
 *    [[GetContentTypes? &format=`json`]]
 * 
 * 3. Custom template for each content type:
 *    [[GetContentTypes? &tpl=`contentTypeRowTpl`]]
 *    
 *    Example chunk 'contentTypeRowTpl':
 *    <li>
*    <span class="name">[[+name]]</span>
    <span class="id">#[[+id]]</span>
 *   <span class="mime">[[+mime_type]]</span>
  *  <span class="extensions">[[+file_extensions]]</span>
  *  <p class="description">[[+description]]</p>
  * </li>
 * 
 * 4. Custom template with wrapper:
 *    [[GetContentTypes? &tpl=`contentTypeRowTpl` &wrapperTpl=`contentTypeWrapperTpl`]]
 *    
 *    Example chunk 'contentTypeWrapperTpl':
 *    <ul>[[+output]]</ul>
 */

// Get parameters
$format = $modx->getOption('format', $scriptProperties, 'array');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$wrapperTpl = $modx->getOption('wrapperTpl', $scriptProperties, '');

// Get all content types
$contentTypes = $modx->getCollection('modContentType');

// Prepare the output array
$output = array();
foreach ($contentTypes as $contentType) {
	$output[] = array(
		'id' => $contentType->get('id'),
		'name' => $contentType->get('name'),
		'description' => $contentType->get('description'),
		'mime_type' => $contentType->get('mime_type'),
		'file_extensions' => $contentType->get('file_extensions'),
		'binary' => $contentType->get('binary')
	);
}

// If a template chunk is specified, process each item through it
if (!empty($tpl)) {
	$processedOutput = '';
	foreach ($output as $item) {
		$processedOutput .= $modx->getChunk($tpl, $item);
	}

	// If wrapper template is specified, wrap the output
	if (!empty($wrapperTpl)) {
		$processedOutput = $modx->getChunk($wrapperTpl, array('output' => $processedOutput));
	}

	return $processedOutput;
}

// If no template, return based on format
switch ($format) {
	case 'json':
		return json_encode($output, JSON_PRETTY_PRINT);

	case 'array':
	default:
		return print_r($output, true);
}
