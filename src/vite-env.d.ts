/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_DATASET_URL?: string
  readonly VITE_DATASET_BASE_URL?: string
  readonly VITE_BASE_PATH?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
