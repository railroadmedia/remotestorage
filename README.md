

remotestorage
==========================================

Wrapper for slightly easier use of [*`league/flysystem`*](https://github.com/thephpleague/flysystem) with AWS S3 by our Laravel application.

- [remotestorage](#remotestorage)
  * [Installation, Configuration, Use](#installation--configuration--use)
    + [Installation](#installation)
    + [Configuration](#configuration)
    + [Use](#use)
  * [RemoteStorageService](#remotestorageservice)
    + [put](#put)
      - [Usage Example](#usage-example)
      - [Parameters](#parameters)
      - [Responses](#responses)
    + [read](#read)
      - [Usage Example](#usage-example-1)
      - [Parameters](#parameters-1)
      - [Responses](#responses-1)
    + [exists](#exists)
      - [Usage Example](#usage-example-2)
      - [Parameters](#parameters-2)
      - [Responses](#responses-2)
    + [delete](#delete)
      - [Usage Example](#usage-example-3)
      - [Parameters](#parameters-3)
      - [Responses](#responses-3)
    + [create_dir](#create-dir)
    + [rename](#rename)
    + [copy](#copy)
    + [get_mimetype](#get-mimetype)
    + [get_timestamp](#get-timestamp)
    + [get_size](#get-size)
    + [delete_dir](#delete-dir)
  
<!-- ecotrust-canada.github.io/markdown-toc -->



Installation, Configuration, Use
--------------------------------





### Installation

Run `$ composer vendor:publish` to copy the package's configuration file "*/config/remotestorage.php*" to your application's "*/config*" directory.

*(assuming you're using Composer, Laravel, and AWS S3)*





### Configuration

Define the following environmental variables with appropriate values:

* *AWS_ACCESS_KEY_ID*
* *AWS_SECRET_ACCESS_KEY*
* *AWS_DEFAULT_REGION*
* *AWS_BUCKET*

Add the service provider (`\Railroad\RemoteStorage\Providers\RemoteStorageServiceProvider`) to the `'providers` array in you application's */config/app.php*:

```php
'providers' => [
    # ...
    \Railroad\RemoteStorage\Providers\RemoteStorageServiceProvider::class,
]
```

Run `$ php artisan vendor:publish` to copy the config file and create a *remotestorage.php* file in your application's */config* directory. This will take the values you supplied in the *.env* file and pass them needed.


### Automated Tests For This Package
Copy `.env.testing.example` to just `.env.testing`. Add your s3 aws details. DO NOT USE A BUCKET WHICH CANNOT BE WIPED. 
Random items from the bucket may be deleted during testing.


### Use

Inject the `Railroad\RemoteStorage\Services\RemoteStorageService` class where needed

```php
/** @var Railroad\RemoteStorage\Services\RemoteStorageService $remoteStorageService */
protected $remoteStorageService;

public function __constructor(Railroad\RemoteStorage\Services\RemoteStorageService $remoteStorageService){
    $this->remoteStorageService = $remoteStorageService;
}
```

Include namespace at top of file:

```php
use Railroad\RemoteStorage\Services;
```

... to save yourself having to specify the namespace everywhere:

```php
/** @var RemoteStorageService $remoteStorageService */
protected $remoteStorageService;

public function __constructor(RemoteStorageService $remoteStorageService){
    $this->remoteStorageService = $remoteStorageService;
}
```


<!--
functionality described by tests:
--><!--
* put
* read
* exists
* delete
* create_dir
* rename
* copy
* get_mimetype
* get_timestamp
* get_size
* delete_dir
-->






RemoteStorageService
--------------------

All methods below are *public*.





### put

#### Usage Example(s)

```php
$upload = $this->remoteStorageService->put($filenameRelative, $filenameAbsolute);
```

```php
/** Upload product thumbnail on remote storage using remotestorage package.
 * Throw an error JSON response if the upload failed or return the uploaded thumbnail url.
 *
 * @param Request $request
 * @return JsonResponse
 */
public function uploadThumbnail(Request $request)
{
    $target = $request->get('target');
    $upload = $this->remoteStorageService->put($target, $request->file('file'));

    throw_if(
        (!$upload),
        new JsonResponse('Upload product thumbnail failed', 400)
    );

    return new JsonResponse(
        $this->remoteStorageService->url($target), 201
    );
}
```

#### Parameters

| # |  name             |  required |  type   |  description                    | 
|---|-------------------|-----------|---------|---------------------------------| 
| 1 |  filenameRelative |  yes      |  string |  name to save file as on remote | 
| 2 |  filenameAbsolute |  yes      |  string |  path to file to upload         | 

<!--
#, name, required, type, description
1, filenameRelative, yes, string, name to save file as on remote
2, filenameAbsolute, yes, string, path to file to upload
-->


#### Responses

| outcome    |  return data type |  return data value | 
|------------|-------------------|--------------------| 
| succeeded  |  boolean          |  true              | 
| failed     |  boolean          |  false             |  

<!--
outcome, return data type, return data value
succeeded , boolean , true 
failed , boolean , false 
-->






### read

#### Usage Example(s)

```php
$file = $this->remoteStorageService->read($filenameRelative);
```

#### Parameters

| #  |  name             |  required |  type    |  description                        | 
|----|-------------------|-----------|----------|-------------------------------------| 
| 1  |  filenameRelative |  yes      |  string  |  path to file name from bucket root | 
 
<!--
#, name, required, type, description
1 , filenameRelative, yes, string , path to file name from bucket root  
-->

#### Responses

| outcome   |  return data type |  return data value (example)                                                                                                                                                     |  notes about return data  | 
|-----------|-------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------| 
| failed    |  boolean          |  false                                                                                                                                                                           |                           | 
| succeeded |  string           |  `"b"""Ø à\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00 ■\x00;CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), quality = 70\n █\x00C\x00\n\x07\x07\x08\x07\x06\n\x08\x08\x08\v\n\n"` |  Raw image data as string | 

<!--
outcome, return data type, return data value (example), notes about return data
failed, boolean, false, 
succeeded, string, `"b"""Ø à\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00 ■\x00;CREATOR: gd-jpeg v1.0 (using IJG JPEG v62), quality = 70\n █\x00C\x00\n\x07\x07\x08\x07\x06\n\x08\x08\x08\v\n\n"`, Raw image data as string
-->






### exists

#### Usage Example(s)

```php
$exists = $this->remoteStorageService->exists('foo/bar.jpg');
```

```php
/** 
 * @param Request $request
 * @return JsonResponse
 */
public function uploadThumbnailIfDoesNotAlreadyExist(Request $request)
{
    $target = 'foo/' . $request->get('target');    
    if(!$this->remoteStorageService->exists('foo/')){
        $upload = $this->remoteStorageService->put($target, $request->file('file'));
        throw_if((!$upload), new JsonResponse('Upload product thumbnail failed', 400));
    }
    return new JsonResponse(['exists' => true]);
}
```

#### Parameters

| #  |  name             |  required |  type    |  description                        | 
|----|-------------------|-----------|----------|-------------------------------------| 
| 1  |  filenameRelative |  yes      |  string  |  path to file name from bucket root | 
 
<!--
#, name, required, type, description
1 , filenameRelative, yes, string , path to file name from bucket root
-->


#### Responses

| outcome        |  return data type |  return data value | 
|----------------|-------------------|--------------------| 
| exists         |  boolean          |  true              | 
| does not exist |  boolean          |  false             | 

<!--   
outcome, return data type, return data value
exists, boolean , true 
does not exist, boolean , false 
-->



### delete


#### Usage Example(s)

```php
$this->remoteStorageService->delete('foo/bar.jpg');
```

```php
public function deleteThumbnail(Request $request)
{
    $target = $request->get('target');    
    $delete = $this->remoteStorageService->delete('foo/' . $target);
    throw_if((!$delete), new JsonResponse('product thumbnail deletion failed', 400));
    return new JsonResponse(['deleted' => true]);
}
```

#### Parameters

| #  |  name             |  required |  type    |  description                        | 
|----|-------------------|-----------|----------|-------------------------------------| 
| 1  |  filenameRelative |  yes      |  string  |  path to file name from bucket root | 
 
<!--
#, name, required, type, description
1 , filenameRelative, yes, string , path to file name from bucket root
-->


#### Responses

| outcome        |  return data type |  return data value | 
|----------------|-------------------|--------------------| 
| exists         |  boolean          |  true              | 
| does not exist |  boolean          |  false             | 

<!--   
outcome, return data type, return data value
exists, boolean , true 
does not exist, boolean , false 
-->






### create_dir

*\[TODO\]*








### rename

*\[TODO\]*








### copy

*\[TODO\]*








### get_mimetype

*\[TODO\]*








### get_timestamp

*\[TODO\]*








### get_size

*\[TODO\]*








### delete_dir

*\[TODO\]*


