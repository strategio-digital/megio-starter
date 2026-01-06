FROM jzaplet/documan:2.9.0-alpha

# Copy your documentation into the image
COPY ./docs /documan/data/docs
COPY ./README.md /documan/data/README.md

# Set working directory
# Important for relative paths in DOCUMAN_DOCS_FILES or DOCUMAN_EXCLUDED_FILES
WORKDIR /documan/data

# Configure your project
ENV DOCUMAN_PROJECT_NAME='Megio.dev'
ENV DOCUMAN_DOCS_FILES='**/*.md'
ENV DOCUMAN_EXCLUDED_FILES=''
ENV DOCUMAN_HTTP_PORT=3000
# ENV DOCUMAN_OPENAI_API_KEY='sk-...'
ENV DOCUMAN_OPENAI_EMBEDDING_MODEL='text-embedding-3-small'
ENV DOCUMAN_CHUNK_MAX_LEN=250
# ENV DOCUMAN_LICENSE_KEY='eyJ...'

# Validate and build at image build time
# If validation fails, the build fails - broken docs won't be deployed
RUN /documan/bin/documan lint
RUN /documan/bin/documan import
# Optional: Enable semantic search for MCP server (requires DOCUMAN_OPENAI_API_KEY)
# RUN /documan/bin/documan vectorize

CMD ["/documan/bin/documan", "serve"]