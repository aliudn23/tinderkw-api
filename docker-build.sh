#!/bin/bash

echo "🐳 Building Docker image for TinderKW API..."
echo "=============================================="
echo ""

# Build the Docker image
docker build -t tinderkw-api:latest .

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Docker image built successfully!"
    echo ""
    echo "📝 To run the container locally:"
    echo "   docker run -p 8000:80 --env-file .env tinderkw-api:latest"
    echo ""
    echo "📝 To test with docker-compose:"
    echo "   docker-compose up"
    echo ""
    echo "📝 To push to a registry:"
    echo "   docker tag tinderkw-api:latest your-registry/tinderkw-api:latest"
    echo "   docker push your-registry/tinderkw-api:latest"
    echo ""
else
    echo ""
    echo "❌ Docker build failed!"
    echo "Please check the error messages above."
    exit 1
fi
