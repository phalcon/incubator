<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Anton Kornilov <kachit@yandex.ru>                             |
  +------------------------------------------------------------------------+
*/

/**
 * Http request methods
 *
 * @package Phalcon\Http\Request
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
 */
namespace Phalcon\Http\Request;

class Method
{
    /**
     * The GET method means retrieve whatever information (in the form of an entity)
     * is identified by the Request-URI. If the Request-URI refers to a
     * data-producing process, it is the produced data which shall be returned
     * as the entity in the response and not the source text of the process,
     * unless that text happens to be the output of the process.
     */
    const GET = 'GET';

    /**
     * The POST method is used to request that the origin server accept
     * the entity enclosed in the request as a new subordinate of the resource identified
     * by the Request-URI in the Request-Line. POST is designed to allow a uniform method
     * to cover the following functions:
     *    - Annotation of existing resources;
     *    - Posting a message to a bulletin board, newsgroup, mailing list,
     *      or similar group of articles;
     *    - Providing a block of data, such as the result of submitting a
     *      form, to a data-handling process;
     *    - Extending a database through an append operation.
     */
    const POST = 'POST';

    /**
     * The PUT method requests that the enclosed entity be stored under
     * the supplied Request-URI. If the Request-URI refers to an already existing resource,
     * the enclosed entity SHOULD be considered as a modified version of the one residing
     * on the origin server. If the Request-URI does not point to an existing resource,
     * and that URI is capable of being defined as a new resource by the requesting user agent,
     * the origin server can create the resource with that URI. If a new resource is created,
     * the origin server MUST inform the user agent via the 201 (Created) response.
     * If an existing resource is modified, either the 200 (OK) or 204 (No Content) response
     * codes SHOULD be sent to indicate successful completion of the request.
     * If the resource could not be created or modified with the Request-URI,
     * an appropriate error response SHOULD be given that reflects the nature of the problem.
     * The recipient of the entity MUST NOT ignore any Content-* (e.g. Content-Range) headers
     * that it does not understand or implement and MUST return a 501 (Not Implemented)
     * response in such cases.
     */
    const PUT = 'PUT';

    /**
     * The PATCH method requests that a set of changes described in the request entity
     * be applied to the resource identified by the Request-URI.
     * The set of changes is represented in a format called a "patch document"
     * identified by a media type. If the Request-URI does not point to an existing resource,
     * the server MAY create a new resource, depending on the patch document type
     * (whether it can logically modify a null resource) and permissions, etc.
     */
    const PATCH = 'PATCH';

    /**
     * The DELETE method requests that the origin server delete the resource
     * identified by the Request-URI. This method MAY be overridden by human intervention
     * (or other means) on the origin server. The client cannot be guaranteed
     * that the operation has been carried out, even if the status code returned
     * from the origin server indicates that the action has been completed successfully.
     * However, the server SHOULD NOT indicate success unless, at the time the
     * response is given, it intends to delete the resource or move it to an
     * inaccessible location.
     */
    const DELETE = 'DELETE';

    /**
     * The HEAD method is identical to GET except that the server MUST NOT return
     * a message-body in the response. The meta information contained in the HTTP headers
     * in response to a HEAD request SHOULD be identical to the information sent in response
     * to a GET request. This method can be used for obtaining meta information about the
     * entity implied by the request without transferring the entity-body itself.
     * This method is often used for testing hypertext links for validity, accessibility,
     * and recent modification.
     */
    const HEAD = 'HEAD';

    /**
     * The OPTIONS method represents a request for information about the communication
     * options available on the request/response chain identified by the Request-URI.
     * This method allows the client to determine the options and/or requirements associated
     * with a resource, or the capabilities of a server, without implying a resource action
     * or initiating a resource retrieval.
     */
    const OPTIONS = 'OPTIONS';

    /**
     * The TRACE method is used to invoke a remote, application-layer loop- back
     * of the request message. The final recipient of the request SHOULD reflect the message
     * received back to the client as the entity-body of a 200 (OK) response.
     * The final recipient is either the origin server or the first proxy or gateway
     * to receive a Max-Forwards value of zero (0) in the request (see section 14.31).
     * A TRACE request MUST NOT include an entity.
     */
    const TRACE = 'TRACE';

    /**
     * This specification reserves the method name CONNECT for use with a proxy
     * that can dynamically switch to being a tunnel
     */
    const CONNECT = 'CONNECT';
}
