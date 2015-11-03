<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
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
 * Http response status codes
 *
 * @package Phalcon\Http\Response
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
namespace Phalcon\Http\Response;

class StatusCode
{
    // Informational 1xx

    /**
     * This means that the server has received the request headers,
     * and that the client should proceed to send the request body
     * (in the case of a request for which a body needs to be sent; for example,
     * a POST request). If the request body is large, sending it to a server when
     * a request has already been rejected based upon inappropriate headers
     * is inefficient. To have a server check if the request could be accepted
     * based on the request's headers alone, a client must send Expect:
     * 100-continue as a header in its initial request and check
     * if a 100 Continue status code is received in response before continuing
     * (or receive 417 Expectation Failed and not continue)
     */
    const CONTINUES = 100;

    /**
     * This means the requester has asked the server to switch protocols
     * and the server is acknowledging that it will do so
     */
    const SWITCHING_PROTOCOLS = 101;

    // Success 2xx

    /**
     * The request has succeeded. The information returned with the response is
     * dependent on the method used in therequest, for example:
     * GET an entity corresponding to the requested resource is sent in the
     * response;
     * HEAD the entity-header fields corresponding to the requested resource
     * are sent in the response without any message-body;
     * POST an entity describing or containing the result of the action;
     * TRACE an entity containing the request message as received by the end
     * server.
     */
    const OK = 200;

    /**
     * The request has been fulfilled and resulted in a new resource being
     * created. The newly created resource can be referenced by the URI(s)
     * returned in the entity of the response, with the most specific URI for
     * the resource given by a Location header field.
     * The response SHOULD include an entity containing a list of resource
     * characteristics and location(s) from which the user or user agent can
     * choose the one most appropriate. The entity format is specified by the
     * media type given in the Content-Type header field. The origin server
     * MUST create the resource before returning the 201 status code. If the
     * action cannot be carried out immediately, the server SHOULD respond with
     * 202 (Accepted) response instead.
     */
    const CREATED = 201;

    /**
     * The request has been accepted for processing, but the processing has not
     * been completed. The request might or might not eventually be acted upon,
     * as it might be disallowed when processing actually takes place. There
     * is no facility for re-sending a status code from an asynchronous
     * operation such as this.
     *
     * The 202 response is intentionally non-committal. Its purpose is to allow
     * a server to accept a request for some other process (perhaps a
     * batch-oriented process that is only run once per day) without requiring
     * that the user agent's connection to the server persist until the
     * process is completed. The entity returned with this response SHOULD
     * include an indication of the request's current status and either a
     * pointer to a status monitor or some estimate of when the user can expect
     * the request to be fulfilled.
     */
    const ACCEPTED = 202;

    /**
     * The returned metainformation in the entity-header is not the definitive
     * set as available from the origin server, but is gathered from a local or
     * a third-party copy. The set presented MAY be a subset or superset of the
     * original version. For example, including local annotation information
     * about the resource might result in a superset of the metainformation
     * known by the origin server. Use of this response code is not required
     * and is only appropriate when the response would otherwise be 200 (OK).
     */
    const NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The server has fulfilled the request but does not need to return an
     * entity-body, and might want to return updated metainformation. The
     * response MAY include new or updated metainformation in the form of
     * entity-headers, which if present SHOULD be associated with the requested
     * variant.
     * If the client is a user agent, it SHOULD NOT change its document view
     * from that which caused the request to be sent. This response is
     * primarily intended to allow input for actions to take place without
     * causing a change to the user agent's active document view, although any
     * new or updated metainformation SHOULD be applied to the document
     * currently in the user agent's active view.
     *
     * The 204 response MUST NOT include a message-body, and thus is always
     * terminated by the first empty line after the header fields.
     */
    const NO_CONTENT = 204;

    /**
     * The server has fulfilled the request and the user agent SHOULD reset
     * the document view which caused the request to be sent. This response is
     * primarily intended to allow input for actions to take place via user
     * input, followed by a clearing of the form in which the input is given
     * so that the user can easily initiate another input action. The response
     * MUST NOT include an entity.
     */
    const RESET_CONTENT = 205;

    /**
     * The server has fulfilled the partial GET request for the resource. The
     * request MUST have included a Range header field indicating the desired
     * range, and MAY have included an If-Range header field (section 14.27) to
     * make the request conditional.
     * The response MUST include the following header fields:
     *
     * - Either a Content-Range header field (section 14.16) indicating
     *   the range included with this response, or a multipart/byteranges
     *   Content-Type including Content-Range fields for each part. If a
     *   Content-Length header field is present in the response, its
     *   value MUST match the actual number of OCTETs transmitted in the
     *   message-body.
     * - Date
     * - ETag and/or Content-Location, if the header would have been sent in a
     *   200 response to the same request
     * - Expires, Cache-Control, and/or Vary, if the field-value might differ
     *   from that sent in any previous response for the same variant
     *
     * If the 206 response is the result of an If-Range request that used a
     * strong cache validator (see section 13.3.3), the response SHOULD NOT
     * include other entity-headers. If the response is the result of an
     * If-Range request that used a weak validator, the response MUST NOT
     * include other entity-headers; this prevents inconsistencies between
     * cached entity-bodies and updated headers. Otherwise, the response MUST
     * include all of the entity-headers that would have been returned with a
     * 200 (OK) response to the same request.
     * A cache MUST NOT combine a 206 response with other previously cached
     * content if the ETag or Last-Modified headers do not match exactly, see 13.5.4.
     *
     * A cache that does not support the Range and Content-Range headers
     * MUST NOT cache 206 (Partial) responses.
     */
    const PARTIAL_CONTENT = 206;

    /**
     * The message body that follows is an XML message and can contain
     * a number of separate response codes, depending on how many sub-requests were made
     * (WebDAV; RFC 4918)
     */
    const MULTI_STATUS = 207;

    /**
     * The members of a DAV binding have already been enumerated
     * in a previous reply to this request, and are not being included again
     * (WebDAV; RFC 5842)
     */
    const ALREADY_REPORTED = 208;

    /**
     * The server has fulfilled a request for the resource,
     * and the response is a representation of the result of one or more
     * instance-manipulations applied to the current instance
     * (RFC 3229)
     */
    const IM_USED = 226;

    // Redirection 3xx

    /**
     * The requested resource corresponds to any one of a set of
     * representations, each with its own specific location, and agent-driven
     * negotiation information is being provided so that the user (or user
     * agent) can select a preferred representation and redirect its request
     * to that location.
     * Unless it was a HEAD request, the response SHOULD include an entity
     * containing a list of resource characteristics and location(s) from
     * which the user or user agent can choose the one most appropriate. The
     * entity format is specified by the media type given in the Content-Type
     * header field. Depending upon the format and the capabilities of the
     * user agent, selection of the most appropriate choice MAY be performed
     * automatically. However, this specification does not define any standard
     * for such automatic selection. If the server has a preferred choice of
     * representation, it SHOULD include the specific URI for that
     * representation in the Location field; user agents MAY use the Location
     * field value for automatic redirection. This response is cacheable
     * unless indicated otherwise.
     */
    const MULTIPLE_CHOICES = 300;

    /**
     * The requested resource has been assigned a new permanent URI and any
     * future references to this resource SHOULD use one of the returned URIs.
     * Clients with link editing capabilities ought to automatically re-link
     * references to the Request-URI to one or more of the new references
     * returned by the server, where possible.
     * This response is cacheable unless indicated otherwise.
     * The new permanent URI SHOULD be given by the Location field in the
     * response. Unless the request method was HEAD, the entity of the
     * response SHOULD contain a short hypertext note with a hyperlink to the
     * new URI(s).
     * If the 301 status code is received in response to a request other than
     * GET or HEAD, the user agent MUST NOT automatically redirect the request
     * unless it can be confirmed by the user, since this might change the
     * conditions under which the request was issued.
     *
     *     Note: When automatically redirecting a POST request after
     *     receiving a 301 status code, some existing HTTP/1.0 user agents
     *     will erroneously change it into a GET request.
     */
    const MOVED_PERMANENTLY = 301;

    /**
     * The requested resource resides temporarily under a different URI. Since
     * the redirection might be altered on occasion, the client SHOULD
     * continue to use the Request-URI for future requests. This response is
     * only cacheable if indicated by a Cache-Control or Expires header field.
     * The temporary URI SHOULD be given by the Location field in the
     * response. Unless the request method was HEAD, the entity of the
     * response SHOULD contain a short hypertext note with a hyperlink to the
     * new URI(s).
     * If the 302 status code is received in response to a request other than
     * GET or HEAD, the user agent MUST NOT automatically redirect the request
     * unless it can be confirmed by the user, since this might change the
     * conditions under which the request was issued.
     *
     *     Note: RFC 1945 and RFC 2068 specify that the client is not allowed
     *     to change the method on the redirected request.  However, most
     *     existing user agent implementations treat 302 as if it were a 303
     *     response, performing a GET on the Location field-value regardless
     *     of the original request method. The status codes 303 and 307 have
     *     been added for servers that wish to make unambiguously clear which
     *     kind of reaction is expected of the client.
     */
    const FOUND = 302;

    /**
     * The response to the request can be found under a different URI and
     * SHOULD be retrieved using a GET method on that resource. This method
     * exists primarily to allow the output of a POST-activated script to
     * redirect the user agent to a selected resource. The new URI is not a
     * substitute reference for the originally requested resource. The 303
     * response MUST NOT be cached, but the response to the second
     * (redirected) request might be cacheable.
     * The different URI SHOULD be given by the Location field in the
     * response. Unless the request method was HEAD, the entity of the
     * response SHOULD contain a short hypertext note with a hyperlink to the
     * new URI(s).
     *     Note: Many pre-HTTP/1.1 user agents do not understand the 303
     *     status. When interoperability with such clients is a concern, the
     *     302 status code may be used instead, since most user agents react
     *     to a 302 response as described here for 303.
     */
    const SEE_OTHER = 303;

    /**
     * If the client has performed a conditional GET request and access is
     * allowed, but the document has not been modified, the server SHOULD
     * respond with this status code. The 304 response MUST NOT contain a
     * message-body, and thus is always terminated by the first empty line
     * after the header fields.
     * The response MUST include the following header fields:
     *     - Date, unless its omission is required by section 14.18.1
     * If a clockless origin server obeys these rules, and proxies and clients
     * add their own Date to any response received without one (as already
     * specified by [RFC 2068], section 14.19), caches will operate correctly.
     *     - ETag and/or Content-Location, if the header would have been sent
     *       in a 200 response to the same request
     *     - Expires, Cache-Control, and/or Vary, if the field-value might
     *       differ from that sent in any previous response for the same variant
     *
     * If the conditional GET used a strong cache validator
     * (see section 13.3.3), the response SHOULD NOT include other
     * entity-headers. Otherwise (i.e., the conditional GET used a weak
     * validator), the response MUST NOT include other entity-headers; this
     * prevents inconsistencies between cached entity-bodies and updated
     * headers.
     * If a 304 response indicates an entity not currently cached, then the
     * cache MUST disregard the response and repeat the request without the
     * conditional.
     * If a cache uses a received 304 response to update a cache entry, the
     * cache MUST update the entry to reflect any new field values given in the
     * response.
     */
    const NOT_MODIFIED = 304;

    /**
     * The requested resource MUST be accessed through the proxy given by the
     * Location field. The Location field gives the URI of the proxy. The
     * recipient is expected to repeat this single request via the proxy.
     * 305 responses MUST only be generated by origin servers.
     *     Note: RFC 2068 was not clear that 305 was intended to redirect a
     *     single request, and to be generated by origin servers only.
     *     Not observing these limitations has significant security
     *     consequences.
     */
    const USE_PROXY = 305;

    /**
     * The requested resource resides temporarily under a different URI. Since
     * the redirection MAY be altered on occasion, the client SHOULD continue
     * to use the Request-URI for future requests. This response is only
     * cacheable if indicated by a Cache-Control or Expires header field.
     * The temporary URI SHOULD be given by the Location field in the
     * response. Unless the request method was HEAD, the entity of the
     * response SHOULD contain a short hypertext note with a hyperlink to the
     * new URI(s) , since many pre-HTTP/1.1 user agents do not understand the
     * 307 status. Therefore, the note SHOULD contain the information
     * necessary for a user to repeat the original request on the new URI.
     * If the 307 status code is received in response to a request other than
     * GET or HEAD, the user agent MUST NOT automatically redirect the
     * request unless it can be confirmed by the user, since this might change
     * the conditions under which the request was issued.
     */
    const TEMPORARY_REDIRECT = 307;

    /**
     * The request, and all future requests should be repeated using another URI.
     * 307 and 308 (as proposed) parallel the behaviours of 302 and 301,
     * but do not allow the HTTP method to change. So, for example,
     * submitting a form to a permanently redirected resource may continue smoothly.
     * (RFC 7538)
     */
    const PERMANENT_REDIRECT = 308;

    // Client Error 4xx

    /**
     * The request could not be understood by the server due to malformed
     * syntax. The client SHOULD NOT repeat the request without modifications.
     */
    const BAD_REQUEST = 400;

    /**
     * The request requires user authentication. The response MUST include a
     * WWW-Authenticate header field (section 14.47) containing a challenge
     * applicable to the requested resource. The client MAY repeat the request
     * with a suitable Authorization header field (section 14.8). If the
     * request already included Authorization credentials, then the 401
     * response indicates that authorization has been refused for those
     * credentials. If the 401 response contains the same challenge as the
     * prior response, and the user agent has already attempted authentication
     * at least once, then the user SHOULD be presented the entity that was
     * given in the response, since that entity might include relevant
     * diagnostic information. HTTP access authentication is explained in
     * "HTTP Authentication: Basic and Digest Access Authentication"
     */
    const BAD_UNAUTHORIZED = 401;

    /**
     * This code is reserved for future use.
     */
    const PAYMENT_REQUIRED = 402;

    /**
     * The server understood the request, but is refusing to fulfill it.
     * Authorization will not help and the request SHOULD NOT be repeated. If
     * the request method was not HEAD and the server wishes to make public
     * why the request has not been fulfilled, it SHOULD describe the reason
     * for the refusal in the entity. If the server does not wish to make this
     * information available to the client, the status code 404 (Not Found)
     * can be used instead.
     */
    const FORBIDDEN = 403;

    /**
     * The server has not found anything matching the Request-URI. No
     * indication is given of whether the condition is temporary or permanent.
     * The 410 (Gone) status code SHOULD be used if the server knows, through
     * some internally configurable mechanism, that an old resource is
     * permanently unavailable and has no forwarding address.
     * This status code is commonly used when the server does not wish to
     * reveal exactly why the request has been refused, or when no other
     * response is applicable.
     */
    const NOT_FOUND = 404;

    /**
     * The method specified in the Request-Line is not allowed for the resource
     * identified by the Request-URI. The response MUST include an Allow header
     * containing a list of valid methods for the requested resource.
     */
    const METHOD_NOT_ALLOWED = 405;

    /**
     * The resource identified by the request is only capable of generating
     * response entities which have content characteristics not acceptable
     * according to the accept headers sent in the request.
     *
     * Unless it was a HEAD request, the response SHOULD include an entity
     * containing a list of available entity characteristics and location(s)
     * from which the user or user agent can choose the one most appropriate.
     * The entity format is specified by the media type given in the
     * Content-Type header field. Depending upon the format and the
     * capabilities of the user agent, selection of the most appropriate
     * choice MAY be performed automatically. However, this specification does
     * not define any standard for such automatic selection.
     *     Note: HTTP/1.1 servers are allowed to return responses which are
     *     not acceptable according to the accept headers sent in the request.
     *     In some cases, this may even be preferable to sending a 406
     *     response. User agents are encouraged to inspect the headers of an
     *     incoming response to determine if it is acceptable.
     *
     * If the response could be unacceptable, a user agent SHOULD temporarily
     * stop receipt of more data and query the user for a decision on further
     * actions.
     */
    const NOT_ACCEPTABLE = 406;

    /**
     * This code is similar to 401 (Unauthorized), but indicates that the
     * client must first authenticate itself with the proxy. The proxy MUST
     * return a Proxy-Authenticate header field (section 14.33) containing a
     * challenge applicable to the proxy for the requested resource. The
     * client MAY repeat the request with a suitable Proxy-Authorization
     * header field (section 14.34). HTTP access authentication is explained
     * in "HTTP Authentication: Basic and Digest Access Authentication"
     */
    const PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * The client did not produce a request within the time that the server
     * was prepared to wait. The client MAY repeat the request without
     * modifications at any later time.
     */
    const REQUEST_TIMEOUT = 408;

    /**
     * The request could not be completed due to a conflict with the current
     * state of the resource. This code is only allowed in situations where it
     * is expected that the user might be able to resolve the conflict and
     * resubmit the request. The response body SHOULD include enough
     * information for the user to recognize the source of the conflict.
     * Ideally, the response entity would include enough information for the
     * user or user agent to fix the problem; however, that might not be
     * possible and is not required.
     * Conflicts are most likely to occur in response to a PUT request. For
     * example, if versioning were being used and the entity being PUT
     * included changes to a resource which conflict with those made by an
     * earlier (third-party) request, the server might use the 409 response to
     * indicate that it can't complete the request.
     * In this case, the response entity would likely contain a list of the
     * differences between the two versions in a format defined by the
     * response Content-Type.
     */
    const CONFLICT = 409;

    /**
     * The requested resource is no longer available at the server and no
     * forwarding address is known. This condition is expected to be
     * considered permanent. Clients with link editing capabilities SHOULD
     * delete references to the Request-URI after user approval. If the server
     * does not know, or has no facility to determine, whether or not the
     * condition is permanent, the status code 404 (Not Found) SHOULD be used
     * instead. This response is cacheable unless indicated otherwise.
     *
     * The 410 response is primarily intended to assist the task of web
     * maintenance by notifying the recipient that the resource is
     * intentionally unavailable and that the server owners desire that
     * remote links to that resource be removed. Such an event is common for
     * limited-time, promotional services and for resources belonging to
     * individuals no longer working at the server's site. It is not necessary
     * to mark all permanently unavailable resources as "gone" or to keep the
     * mark for any length of time -- that is left to the discretion of the
     * server owner.
     */
    const GONE = 410;

    /**
     * The server refuses to accept the request without a defined
     * Content-Length. The client MAY repeat the request if it adds a valid
     * Content-Length header field containing the length of the message-body
     * in the request message.
     */
    const LENGTH_REQUIRED = 411;

    /**
     * The precondition given in one or more of the request-header fields
     * evaluated to false when it was tested on the server. This response code
     * allows the client to place preconditions on the current resource
     * meta information (header field data) and thus prevent the requested
     * method from being applied to a resource other than the one intended.
     */
    const PRECONDITION_FAILED = 412;

    /**
     * The server is refusing to process a request because the request entity
     * is larger than the server is willing or able to process. The server MAY
     * close the connection to prevent the client from continuing the request.
     * If the condition is temporary, the server SHOULD include a Retry- After
     * header field to indicate that it is temporary and after what time the
     * client MAY try again.
     */
    const REQUEST_ENTITY_TOO_LARGE = 413;

    /**
     * The server is refusing to service the request because the Request-URI
     * is longer than the server is willing to interpret. This rare condition
     * is only likely to occur when a client has improperly converted a POST
     * request to a GET request with long query information, when the client
     * has descended into a URI "black hole" of redirection (e.g., a
     * redirected URI prefix that points to a suffix of itself), or when the
     * server is under attack by a client attempting to exploit security holes
     * present in some servers using fixed-length buffers for reading or
     * manipulating the Request-URI.
     */
    const REQUEST_URI_TOO_LONG = 414;

    /**
     * The server is refusing to service the request because the entity of the
     * request is in a format not supported by the requested resource for the
     * requested method.
     */
    const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * A server SHOULD return a response with this status code if a request
     * included a Range request-header field (section 14.35), and none of the
     * range-specifier values in this field overlap the current extent of the
     * selected resource, and the request did not include an If-Range
     * request-header field. (For byte-ranges, this means that the
     * first-byte-pos of all of the byte-range-spec values were greater than
     * the current length of the selected resource.)
     * When this status code is returned for a byte-range request, the
     * response SHOULD include a Content-Range entity-header field specifying
     * the current length of the selected resource (see section 14.16). This
     * response MUST NOT use the multipart/byteranges content-type.
     */
    const REQUEST_RANGE_NOT_SATISFIABLE = 416;

    /**
     * The expectation given in an Expect request-header field (see section
     * 14.20) could not be met by this server, or, if the server is a proxy,
     * the server has unambiguous evidence that the request could not be met
     * by the next-hop server.
     */
    const EXPECTATION_FAILED = 417;

    /**
     * The HTCPCP server is a teapot; the resulting entity may be short and
     * stout. Demonstrations of this behaviour exist.
     */
    const I_AM_A_TEAPOT = 418;

    /**
     * The 422 (Unprocessable Entity) status code means the server understands
     * the content type of the request entity (hence a
     * 415[Unsupported Media Type] status code is inappropriate), and the
     * syntax of the request entity is correct (thus a 400 (Bad Request)
     * status code is inappropriate) but was unable to process the contained
     * instructions. For example, this error condition may occur if an XML
     * request body contains well-formed (i.e., syntactically correct), but
     * semantically erroneous, XML instructions.
     */
    const UNPROCESSABLE_ENTITY = 422;

    /**
     * The 423 (Locked) status code means the source or destination resource
     * of a method is locked.  This response SHOULD contain an appropriate
     * precondition or post-condition code, such as 'lock-token-submitted' or
     * 'no-conflicting-lock'.
     */
    const LOCKED = 423;

    /**
     * The 424 (Failed Dependency) status code means that the method could not
     * be performed on the resource because the requested action
     * depended on another action and that action failed. For example, if a
     * command in a PROPPATCH method fails then, at minimum, the rest
     * of the commands will also fail with 424 (Failed Dependency).
     */
    const FAILED_DEPENDENCY = 424;

    /**
     * The Upgrade response header field advertises possible protocol upgrades
     * a server MAY accept. In conjunction with the "426 Upgrade Required"
     * status code, a server can advertise the exact protocol upgrades that a
     * client MUST accept to complete the request. The server MAY include an
     * Upgrade header in any response other than 101 or 426 to indicate a
     * willingness to switch to any (combination) of the protocols listed.
     */
    const UPDATE_REQUIRED = 426;

    /**
     * The origin server requires the request to be conditional. Its typical
     * use is to avoid the "lost update" problem, where a client GETs a
     * resource's state, modifies it, and PUTs it back to the server, when
     * meanwhile a third party has modified the state on the server, leading to
     * a conflict.  By requiring requests to be conditional, the server can
     * assure that clients are working with the correct copies.
     */
    const PRECONDITION_REQUIRED = 428;

    /**
     * The user has sent too many requests in a given amount of time.
     */
    const TOO_MANY_REQUESTS = 429;

    /**
     * The server is unwilling to process the request because its header fields
     * are too large.
     */
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    // Server Error 5xx

    /**
     * The server encountered an unexpected condition which prevented it from
     * fulfilling the request.
     */
    const INTERNAL_SERVER_ERROR = 500;

    /**
     * The server does not support the functionality required to fulfill the
     * request. This is the appropriate response when the server does not
     * recognize the request method and is not capable of supporting it for
     * any resource.
     */
    const NOT_IMPLEMENTED = 501;

    /**
     * The server, while acting as a gateway or proxy, received an invalid
     * response from the upstream server it accessed in attempting to fulfill
     * the request.
     */
    const BAD_GATEWAY = 502;

    /**
     * The server is currently unable to handle the request due to a temporary
     * overloading or maintenance of the server. The implication is that this
     * is a temporary condition which will be alleviated after some delay. If
     * known, the length of the delay MAY be indicated in a Retry-After header.
     * If no Retry-After is given, the client SHOULD handle the response as it
     * would for a 500 response.
     *    Note: The existence of the 503 status code does not imply that a
     *    server must use it when becoming overloaded. Some servers may wish to
     *    simply refuse the connection.
     */
    const SERVICE_UNAVAILABLE = 503;

    /**
     * The server, while acting as a gateway or proxy, did not receive a
     * timely response from the upstream server specified by the URI (e.g.
     * HTTP, FTP, LDAP) or some other auxiliary server (e.g. DNS) it needed to
     * access in attempting to complete the request.
     *    Note: Note to implementors: some deployed proxies are known to
     *    return 400 or 500 when DNS lookups time out.
     */
    const GATEWAY_TIMEOUT = 504;

    /**
     * The server does not support, or refuses to support, the HTTP protocol
     * version that was used in the request message. The server is indicating
     * that it is unable or unwilling to complete the request using the same
     * major version as the client, as described in section 3.1, other than
     * with this error message.
     * The response SHOULD contain an entity describing why that version is
     * not supported and what other protocols are supported by that server.
     */
    const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * Transparent content negotiation for the request results in a circular
     * reference.
     */
    const VARIANT_ALSO_NEGOTIATES = 506;

    /**
     * The server is unable to store the representation needed to complete the
     * request.
     */
    const INSUFFICIENT_STORAGE = 507;

    /**
     * The server detected an infinite loop while processing the request
     * (sent in lieu of 208).
     */
    const LOOP_DETECTED = 508;

    /**
     * This status code, while used by many servers, is not specified in any
     * RFCs.
     */
    const BANDWIDTH_LIMIT_EXCEED = 509;

    /**
     * Further extensions to the request are required for the server to fulfill
     * it.
     */
    const NOT_EXTENDED = 510;

    /**
     * The client needs to authenticate to gain network access.
     */
    const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * @var array
     */
    protected static $messages = [
        // Informational 1xx
        self::CONTINUES => 'Continue',
        self::SWITCHING_PROTOCOLS => 'Switching Protocols',

        // Success 2xx
        self::OK => 'OK',
        self::CREATED => 'Created',
        self::ACCEPTED => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::NO_CONTENT => 'No Content',
        self::RESET_CONTENT => 'Reset Content',
        self::PARTIAL_CONTENT => 'Partial Content',
        self::MULTI_STATUS => 'Multi-Status',
        self::ALREADY_REPORTED => 'Already Reported',
        self::IM_USED => 'IM Used',

        // Redirection 3xx
        self::MULTIPLE_CHOICES => 'Multiple Choices',
        self::MOVED_PERMANENTLY => 'Moved Permanently',
        self::FOUND => 'Found', // 1.1
        self::SEE_OTHER => 'See Other',
        self::NOT_MODIFIED => 'Not Modified',
        self::USE_PROXY => 'Use Proxy',
        // 306 is deprecated but reserved
        self::TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::PERMANENT_REDIRECT => 'Permanent Redirect',

        // Client Error 4xx
        self::BAD_REQUEST => 'Bad Request',
        self::BAD_UNAUTHORIZED => 'Unauthorized',
        self::PAYMENT_REQUIRED => 'Payment Required',
        self::FORBIDDEN => 'Forbidden',
        self::NOT_FOUND => 'Not Found',
        self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::NOT_ACCEPTABLE => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT => 'Request Timeout',
        self::CONFLICT => 'Conflict',
        self::GONE => 'Gone',
        self::LENGTH_REQUIRED => 'Length Required',
        self::PRECONDITION_FAILED => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::REQUEST_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED => 'Expectation Failed',
        self::I_AM_A_TEAPOT => "I'm a teapot",
        self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        self::LOCKED => 'Locked',
        self::FAILED_DEPENDENCY => 'Failed Dependency',
        self::UPDATE_REQUIRED => 'Upgrade Required',
        self::PRECONDITION_REQUIRED => 'Precondition Required',
        self::TOO_MANY_REQUESTS => 'Too Many Requests',
        self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',

        // Server Error 5xx
        self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::NOT_IMPLEMENTED => 'Not Implemented',
        self::BAD_GATEWAY => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        self::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        self::INSUFFICIENT_STORAGE => 'Insufficient Storage',
        self::LOOP_DETECTED => 'Loop Detected',
        self::BANDWIDTH_LIMIT_EXCEED => 'Bandwidth Limit Exceeded',
        self::NOT_EXTENDED => 'Not Extended',
        self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    /**
     * Get response message
     *
     * @param int $code
     * @return string
     */
    public static function message($code)
    {
        return (isset(self::$messages[$code])) ? self::$messages[$code] : '';
    }
}
