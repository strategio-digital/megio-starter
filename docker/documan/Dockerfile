FROM jzaplet/documan:2.4.2-alpha

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
ENV DOCUMAN_DB_PATH='/documan/db/docs.db'
ENV DOCUMAN_HTTP_PORT=3000
ENV DOCUMAN_MCP_SEARCH_RESULTS_LIMIT=20
ENV DOCUMAN_MAX_MARKDOWN_CHUNK_LEN=250

# Validate and build at image build time
# If validation fails, the build fails - broken docs won't be deployed
RUN /documan/bin/documan lint
RUN /documan/bin/documan import

CMD ["/documan/bin/documan", "serve"]