# 📋 OpenAPI Specification - MyRVM-Ecosystem v2.0

## 📍 API Overview

### Server API (MyRVM-Ecosystem-v2)
- **Base URL**: `http://100.123.143.87:8001`
- **Version**: 2.0.0
- **Title**: MyRVM-Ecosystem-v2 API
- **Description**: RESTful API for Reverse Vending Machine management system

### Jetson API (MyCV-Platform)
- **Base URL**: `http://100.117.234.2:5000`
- **Version**: 1.0.0
- **Title**: MyCV-Platform Jetson API
- **Description**: Computer Vision processing API for Jetson devices

---

## 🔧 OpenAPI 3.0 Specification

### Server API OpenAPI Spec
```yaml
openapi: 3.0.3
info:
  title: MyRVM-Ecosystem-v2 API
  description: RESTful API for Reverse Vending Machine management system
  version: 2.0.0
  contact:
    name: MyRVM Support
    email: support@myrvm.com
  license:
    name: MIT
    url: https://opensource.org/licenses/MIT

servers:
  - url: http://100.123.143.87:8001
    description: Production server

security:
  - bearerAuth: []

paths:
  /api/health:
    get:
      summary: Health check
      description: Check API health status
      security: []
      responses:
        '200':
          description: API is healthy
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    example: healthy
                  service:
                    type: string
                    example: MyRVM-Ecosystem-v2
                  version:
                    type: string
                    example: 2.0.0
                  timestamp:
                    type: string
                    format: date-time

  /api/auth/login:
    post:
      summary: User login
      description: Authenticate user and get access token
      security: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - email
                - password
              properties:
                email:
                  type: string
                  format: email
                  example: admin@myrvm.com
                password:
                  type: string
                  example: password123
      responses:
        '200':
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                    example: true
                  user:
                    type: object
                    properties:
                      id:
                        type: integer
                        example: 1
                      name:
                        type: string
                        example: Admin User
                      email:
                        type: string
                        example: admin@myrvm.com
                      role:
                        type: string
                        example: admin
                  token:
                    type: string
                    example: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...

  /api/rvms:
    get:
      summary: Get all RVMs
      description: Retrieve list of all RVM devices
      responses:
        '200':
          description: List of RVMs
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                    example: true
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/RVM'
    post:
      summary: Create new RVM
      description: Create a new RVM device
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateRVMRequest'
      responses:
        '201':
          description: RVM created successfully
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                    example: true
                  message:
                    type: string
                    example: RVM created successfully
                  data:
                    $ref: '#/components/schemas/RVM'

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    RVM:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: RVM-001
        location:
          type: string
          example: Mall Central
        ip_address:
          type: string
          example: 100.117.234.2
        status:
          type: string
          enum: [active, inactive, maintenance]
          example: active
        connection_status:
          type: string
          enum: [connected, disconnected]
          example: connected
        api_status:
          type: string
          enum: [valid, invalid]
          example: valid
        current_load:
          type: integer
          example: 45
        capacity:
          type: integer
          example: 100
        usage_percentage:
          type: number
          format: float
          example: 45.0
        last_ping:
          type: string
          format: date-time
          example: 2025-01-02T10:30:00Z
        api_key:
          type: string
          example: 38bbe1d2ecf75df21546b05340f5878a24e74ed0d8b88d75db1ddeff198380c1

    CreateRVMRequest:
      type: object
      required:
        - name
        - location
        - ip_address
      properties:
        name:
          type: string
          example: RVM-002
        location:
          type: string
          example: Mall North
        ip_address:
          type: string
          example: 100.117.234.3
        address:
          type: string
          example: Jl. Utara No. 123
        latitude:
          type: number
          format: float
          example: -6.200000
        longitude:
          type: number
          format: float
          example: 106.816666

    Error:
      type: object
      properties:
        success:
          type: boolean
          example: false
        error:
          type: string
          example: Error message
        code:
          type: string
          example: ERROR_CODE
        details:
          type: object
          additionalProperties: true
```

### Jetson API OpenAPI Spec
```yaml
openapi: 3.0.3
info:
  title: MyCV-Platform Jetson API
  description: Computer Vision processing API for Jetson devices
  version: 1.0.0
  contact:
    name: MyCV Support
    email: support@mycv.com
  license:
    name: MIT
    url: https://opensource.org/licenses/MIT

servers:
  - url: http://100.117.234.2:5000
    description: Jetson device

security:
  - rvmApiKey: []

paths:
  /api/health:
    get:
      summary: Health check
      description: Check Jetson API health status
      security: []
      responses:
        '200':
          description: API is healthy
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    example: healthy
                  service:
                    type: string
                    example: MyCV-Edge-API
                  version:
                    type: string
                    example: 1.0.0
                  timestamp:
                    type: string
                    format: date-time
                  uptime:
                    type: number
                    example: 3600

  /api/upload:
    post:
      summary: Upload images
      description: Upload images for detection processing
      requestBody:
        required: true
        content:
          multipart/form-data:
            schema:
              type: object
              properties:
                files:
                  type: array
                  items:
                    type: string
                    format: binary
                user_id:
                  type: string
                  example: my_user
                rvm_id:
                  type: string
                  example: "1"
      responses:
        '200':
          description: Upload successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                    example: true
                  session_id:
                    type: string
                    example: session_abc123
                  timestamp:
                    type: string
                    example: 20250102_103000
                  user_id:
                    type: string
                    example: my_user
                  uploaded_files:
                    type: array
                    items:
                      type: object
                      properties:
                        original_name:
                          type: string
                          example: image1.jpg
                        saved_name:
                          type: string
                          example: image1.jpg
                        path:
                          type: string
                          example: /path/to/saved/image1.jpg
                  message:
                    type: string
                    example: Files uploaded successfully. Processing started.
                  status_url:
                    type: string
                    example: /api/process/session_abc123
                  results_url:
                    type: string
                    example: /api/results/session_abc123
                  rvm:
                    type: object
                    properties:
                      id:
                        type: integer
                        example: 1
                      name:
                        type: string
                        example: RVM-001
                      location:
                        type: string
                        example: Mall Central

  /api/process/{session_id}:
    get:
      summary: Get processing status
      description: Get processing status for a session
      parameters:
        - name: session_id
          in: path
          required: true
          schema:
            type: string
          example: session_abc123
      responses:
        '200':
          description: Processing status
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    enum: [processing, completed, failed]
                    example: completed
                  message:
                    type: string
                    example: Detection completed successfully
                  timestamp:
                    type: string
                    example: 20250102_103000
                  user_id:
                    type: string
                    example: my_user
                  rvm_id:
                    type: integer
                    example: 1
                  start_time:
                    type: string
                    format: date-time
                    example: 2025-01-02T10:30:00Z
                  end_time:
                    type: string
                    format: date-time
                    example: 2025-01-02T10:30:15Z

components:
  securitySchemes:
    rvmApiKey:
      type: apiKey
      in: header
      name: X-RVM-API-Key
      description: RVM API key for authentication

  schemas:
    DetectionResult:
      type: object
      properties:
        class_name:
          type: string
          example: plastic_bottle
        confidence:
          type: number
          format: float
          example: 0.95
        bbox:
          type: array
          items:
            type: integer
          example: [100, 200, 300, 400]

    ProcessingResult:
      type: object
      properties:
        detection_summary:
          type: object
          properties:
            total_objects:
              type: integer
              example: 3
            classes_detected:
              type: array
              items:
                type: string
              example: ["plastic_bottle", "glass_bottle", "aluminum_can"]
            confidence_scores:
              type: array
              items:
                type: number
                format: float
              example: [0.95, 0.87, 0.92]
        class_summary:
          type: object
          additionalProperties:
            type: integer
          example:
            plastic_bottle: 1
            glass_bottle: 1
            aluminum_can: 1
        object_count:
          type: integer
          example: 3
        images_processed:
          type: array
          items:
            type: object
            properties:
              image_name:
                type: string
                example: image1
              detections:
                type: array
                items:
                  $ref: '#/components/schemas/DetectionResult'
              detection_count:
                type: integer
                example: 1
              visualizations:
                type: array
                items:
                  type: object
                  properties:
                    type:
                      type: string
                      example: compare
                    file:
                      type: string
                      example: image1-best_pt-compare.png
                    path:
                      type: string
                      example: /output/image1-best_pt-compare.png

    Error:
      type: object
      properties:
        error:
          type: string
          example: Error message
        code:
          type: string
          example: ERROR_CODE
        details:
          type: object
          additionalProperties: true
```

---

## 🔧 Swagger UI Setup

### 1. Server API Swagger UI
```bash
# Install Swagger UI
npm install -g swagger-ui-serve

# Serve Server API spec
swagger-ui-serve server-api.yaml --port 8080
```

### 2. Jetson API Swagger UI
```bash
# Serve Jetson API spec
swagger-ui-serve jetson-api.yaml --port 8081
```

### 3. Docker Swagger UI
```yaml
version: '3.8'
services:
  swagger-ui-server:
    image: swaggerapi/swagger-ui
    ports:
      - "8080:8080"
    environment:
      - SWAGGER_JSON=/app/server-api.yaml
    volumes:
      - ./server-api.yaml:/app/server-api.yaml

  swagger-ui-jetson:
    image: swaggerapi/swagger-ui
    ports:
      - "8081:8080"
    environment:
      - SWAGGER_JSON=/app/jetson-api.yaml
    volumes:
      - ./jetson-api.yaml:/app/jetson-api.yaml
```

---

## 📊 API Documentation

### Server API Documentation
- **Swagger UI**: `http://100.123.143.87:8080`
- **OpenAPI Spec**: `http://100.123.143.87:8001/api/docs`
- **ReDoc**: `http://100.123.143.87:8001/api/redoc`

### Jetson API Documentation
- **Swagger UI**: `http://100.117.234.2:8081`
- **OpenAPI Spec**: `http://100.117.234.2:5000/api/docs`
- **ReDoc**: `http://100.117.234.2:5000/api/redoc`

---

## 🧪 Testing with OpenAPI

### 1. Generate Client SDKs
```bash
# Generate Python client
openapi-generator generate -i server-api.yaml -g python -o ./clients/python-server

# Generate JavaScript client
openapi-generator generate -i server-api.yaml -g javascript -o ./clients/js-server

# Generate Java client
openapi-generator generate -i server-api.yaml -g java -o ./clients/java-server
```

### 2. Generate Server Stubs
```bash
# Generate Laravel server stub
openapi-generator generate -i server-api.yaml -g php-laravel -o ./server-stub

# Generate Flask server stub
openapi-generator generate -i jetson-api.yaml -g python-flask -o ./jetson-stub
```

### 3. Validate API Specs
```bash
# Validate Server API spec
swagger-codegen validate -i server-api.yaml

# Validate Jetson API spec
swagger-codegen validate -i jetson-api.yaml
```

---

## 📝 OpenAPI Best Practices

### 1. Schema Design
- Use descriptive names for schemas
- Include examples for all properties
- Use appropriate data types
- Add validation constraints

### 2. Documentation
- Provide clear descriptions
- Include usage examples
- Document error responses
- Add contact information

### 3. Security
- Define security schemes
- Apply security to endpoints
- Document authentication methods
- Include authorization scopes

### 4. Versioning
- Use semantic versioning
- Maintain backward compatibility
- Document breaking changes
- Provide migration guides

---

**Last Updated**: 2025-01-02  
**Version**: 2.0.0  
**Status**: ✅ COMPLETE OPENAPI SPECIFICATION
