group "default" {
  targets = [
    "prod",
    "test",
  ]
}
variable "PLATFORMS_ALL" {
  default = [
    "linux/arm64",
    "linux/amd64",
    "linux/arm/v7",
    "linux/arm/v6",
    "linux/i386",
  ]
}
variable "PLATFORMS_TEST" {
  default = [
    "linux/amd64",
  ]
}
target "prod" {
  context = "."
  dockerfile = "Dockerfile"
  platforms = PLATFORMS_ALL
  tags = [
    "matthewbaggett/full-control-home-assistant:latest",
    "ghcr.io/matthewbaggett/full-control-home-assistant:latest",
  ]
}
target "test" {
  context = "."
  dockerfile = "Dockerfile"
  platforms = PLATFORMS_TEST
  tags = [
    "ghcr.io/matthewbaggett/full-control-home-assistant:test",
  ]
}
