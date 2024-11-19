<?php
/*
 * GetContentTypes
 * A snippet that returns all MODX content types
 * 
 * PARAMETERS:
 *  &id - Specific content type ID to return [optional] 

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
$contentTypeId = $modx->getOption('id', $scriptProperties, 0); // Add this line



// Get all content types
if ($contentTypeId > 0) {
	$contentTypes = $modx->getObject('modContentType', $contentTypeId);
	$contentTypes = $contentTypes ? array($contentTypes) : array();
} else {
	$contentTypes = $modx->getCollection('modContentType');
}

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

	// Handle both regular chunks and @INLINE chunks
	$isInline = strpos($tpl, '@INLINE') === 0;
	$chunkName = $isInline ? substr($tpl, 8) : $tpl;

	// Only check for chunk existence if it's not an @INLINE chunk
	if (!$isInline && !$modx->getObject('modChunk', array('name' => $tpl))) {
		return 'Error: Chunk "' . $tpl . '" not found';
	}
	// Debug: Check if output array has items
	if (empty($output)) {
		return 'Error: No content types found';
	}
	foreach ($output as $item) {
		$chunk = $isInline
			? $modx->parseChunk('@INLINE ' . $chunkName, $item)
			: $modx->getChunk($tpl, $item);
		$processedOutput .= $chunk;
	}

	if (!empty($wrapperTpl)) {
		$isInlineWrapper = strpos($wrapperTpl, '@INLINE') === 0;
		$wrapperChunkName = $isInlineWrapper ? substr($wrapperTpl, 8) : $wrapperTpl;

		// Only check for chunk existence if it's not an @INLINE chunk
		if (!$isInlineWrapper && !$modx->getObject('modChunk', array('name' => $wrapperTpl))) {
			return 'Error: Wrapper chunk "' . $wrapperTpl . '" not found';
		}

		$processedOutput = $isInlineWrapper
			? $modx->parseChunk('@INLINE ' . $wrapperChunkName, array('output' => $processedOutput))
			: $modx->getChunk($wrapperTpl, array('output' => $processedOutput));
	}

	return $processedOutput;
}

// If no template, return based on format
switch ($format) {
	case 'json':
		return $modx->toJSON($output);
	default:
		return var_export($output, true);
}
