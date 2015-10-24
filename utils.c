#include "utils.h"

void unix_error(const char *msg)
{
	fprintf(stderr, "%s: %s\n", msg, strerror(errno));
	exit(EXIT_FAILURE);
}


void* Malloc(size_t size)
{
	void* ptr = malloc(size);

	if (ptr == NULL)
		unix_error("Memory allocation error");

	return ptr;
}

void* Realloc(void *ptr, size_t size)
{
	ptr = realloc(ptr, size);

	if (ptr == NULL)
		unix_error("Memory allocation error");

	return ptr;
}

pid_t Fork()
{
	pid_t pid = fork();

	if (pid < 0)
		unix_error("Fork error");

	return pid;
}

pid_t Wait(int *status)
{
	pid_t pid;

	if ((pid = wait(status)) < 0)
		unix_error("Wait error");

	return pid;
}

void Kill(pid_t pid, int signum)
{
	int rc;

	if ((rc = kill(pid, signum)) < 0)
		unix_error("Kill error");
}

int Open(char *filename, int flags, mode_t mode)
{
	int fd;

	if ((fd = open(filename, flags, mode)) < 0)
		unix_error("Open error");

	return fd;
}

FILE *Fopen(const char * filename, const char * mode)
{
	FILE *fd;

	if ((fd = fopen(filename, mode)) < 0)
		unix_error("Open error");

	return fd;
}


void Close(int fd)
{
	int ok;

	if ((ok = close(fd)) < 0)
		unix_error("Close error");
}

ssize_t Read(int fd, void *buf, size_t n)
{
	int bytes_read;

	if ((bytes_read = (read(fd, buf, n))) < 0)
		unix_error("Read error");

	return bytes_read;
}

ssize_t Write(int fd, const void *buf, size_t n)
{
	int bytes_written;

	if ((bytes_written = (write(fd, buf, n))) < 0)
		unix_error("Write error");

	return bytes_written;
}

void Stat(const char *filename, struct stat *buf)
{
	int ok;

	if (!buf || (ok = stat(filename, buf)) < 0)
		unix_error("Stat error");
}

void Fstat(int fd, struct stat *buf)
{
	int ok;

	if (!buf || (ok = fstat(fd, buf)) < 0)
		unix_error("Stat error");
}

int Dup2(int oldfd, int newfd)
{
	int fd;

	if ((fd = dup2(oldfd, newfd)) < 0)
		unix_error("Dup2 error");

	return fd;
}

void Pipe(int pipefd[2])
{
	int ok;

	if ((ok = pipe(pipefd)) < 0)
		unix_error("Pipe error");
}