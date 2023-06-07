{ pkgs }: {
	deps = [
  pkgs.nodejs-16_x
  pkgs.code-server
  pkgs.sqlite.bin
  pkgs.php80Packages.composer
  pkgs.php82
	];
}