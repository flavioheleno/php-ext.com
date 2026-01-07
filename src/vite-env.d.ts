/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_DATASET_URL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
