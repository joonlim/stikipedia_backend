#ifndef __UTILITIES_H
#define __UTILITIES_H

/* Includes */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/stat.h>
#include <stdbool.h>
#include <sys/wait.h>
#include <stdarg.h>
#include <errno.h>
#include <fcntl.h>

/**
 * Print out a string to the shell and terminate.
 * 
 * @param msg [input] string to output to shell before program terminates.
 */
void unix_error(const char *msg);

/**
 * Steven-style error-handling wrapper for malloc()
 * 
 * @param size [input] size of desired allocated region
 * @return 	   ptr to allocated region. Must free when finished.
 */
void* Malloc(size_t size);

/**
 * Steven-style error-handling wrapper for realloc()
 *
 * @param ptr  [input] ptr to region to reallocate
 * @param size [input] size of desired allocated region
 * @return 	   ptr to allocated region. Must free when finished.
 */
void* Realloc(void *ptr, size_t size);

/**
 * Steven-style error-handling wrapper for fork()
 *
 * @return	0 to child process, non-zero number to parent process
 */
pid_t Fork();

/**
 * Steven-style error-handling wrapper for wait()
 * 
 * @param  status [output] child status
 *                Checked using macros defined in wait.h
 *                - WIFEXITED, WEXITSTATIS, WIFSIGNALED, WTERMSIG, WIFSTOPPED,
 *                  WSTOPSIG, WIFCONTINUED
 * @return        pid of the child process that terminated.
 */
pid_t Wait(int *status);

/**
 * Steven-style error-handling wrapper for kill()
 * 
 * @param pid    [input] id of process to kill
 * @param signum [input] signal number to be sent
 */
void Kill(pid_t pid, int signum);

/**
 * Steven-style error-handling wrapper for open()
 * 
 * @param  filename [input]    file name to open
 * @param  flags    [input]    flags
 *                  		   O_RDONLY: Reading Only
 *                  		   O_WRONLY: Writing Only
 *                  		   O_RDWF: Reading and Writng
 *                  		   The flags argument can also be ORed with one or
 *                  		   more bit masks that provide additional
 *                  		   instructions for writing:
 *                  		   O_CREAT: If file doesn't exist, create an empty
 *                  		   			version of it.
 *                  		   O_TRUNC: If file already exists, truncate it.
 *                  		   O_APPEND: Before each write operation, set the
 *                  		   			 file position to the end of the file.
 * @param  mode     [optional] permissions setting
 * @return          file descriptor of open file
 */
int Open(char *filename, int flags, mode_t mode);

/**
 * Steven-style error-handling wrapper for fopen()
 * 
 * @param  filename [input]    file name to open
 * @param  flags    [input]    flags
 *                  		   O_RDONLY: Reading Only
 *                  		   O_WRONLY: Writing Only
 *                  		   O_RDWF: Reading and Writng
 *                  		   The flags argument can also be ORed with one or
 *                  		   more bit masks that provide additional
 *                  		   instructions for writing:
 *                  		   O_CREAT: If file doesn't exist, create an empty
 *                  		   			version of it.
 *                  		   O_TRUNC: If file already exists, truncate it.
 *                  		   O_APPEND: Before each write operation, set the
 *                  		   			 file position to the end of the file.
 * @param  mode     [optional] permissions setting
 * @return          file descriptor of open file
 */
FILE *Fopen(const char * filename, const char * mode);

/**
 * Steven-style error-handling wrapper for close()
 * 
 * @param  fd [input] file descriptor of open file to close.
 */
void Close(int fd);

/**
 * Steven-style error-handling wrapper for read()
 *
 * Copies at most n bytes from the current file position of descriptor fd to
 * memory location buf.
 * 
 * @param  fd  [input] file descriptor of open file to read
 * @param  buf [input] buffer to read into
 * @param  n   [input] number of chars to read
 * @return     number of bytes read. Return value of 0 indicates EOF.
 */
ssize_t Read(int fd, void *buf, size_t n);

/**
 * Steven-style error-handling wrapper for write()
 *
 * Copies at most n bytes from memory location buf to the current file position
 * of descriptor fd.
 * 
 * @param  fd     [input] file descriptor of open file to write to
 * @param  buf    [input] buffer to read from
 * @param  size_t [input] number of chars to write
 * @return        number of bytes written
 */
ssize_t Write(int fd, const void *buf, size_t n);

/**
 * Steven-style error-handling wrapper for stat()
 *
 * Takes as input a filename and fills in the members of a stat struct.
 * 
 * @param  filename [input] file name to get stats for
 * @param  buf      [output] stat struct which will contain stats
 */
void Stat(const char *filename, struct stat *buf);

/**
 * Steven-style error-handling wrapper for fstat()
 *
 * Takes as input a file descriptor and fills in the members of a stat struct.
 * 
 * @param  fd  [input] file descriptor of file to get stats for
 * @param  buf [output] stat struct which will contain stats
 */
void Fstat(int fd, struct stat *buf);

/**
 * Steven-style error-handling wrapper for dup2()
 *
 * Copies descriptor table entry oldfd to descriptor table entry newfd,
 * overwriting the previous contents of descriptor table entry newfd. If newfd
 * was already open, then this function clsoes newfd before it copies oldfd.
 *
 * Call in child process to have it print output to file rather than stdout.
 *
 * Use to redirect stdin, stdout, stderr
 * 
 * @param  oldfd [input] old file descriptor to copy over
 * @param  newfd [input] new file descriptor to copy to
 * @return       nonnegative descriptor
 */
int Dup2(int oldfd, int newfd);

/**
 * Steven-style erorr-handling wrapper for pipe()
 *
 * Creates a pipe, a unidirectional data channel that can be used for
 * interprocess communication. The array pipefd is used to return file 
 * descriptors referring to the ends of the pipe.
 * pipefd[0] refers to the read end of the pipe.
 * pipefd[1] refers to the write end of the pipe.
 * Data written to the write en of the pipe is buffered by the kernel until it
 * is read from the read end of the pipe.
 * 
 * @param  pipefd [input] two file descriptors referring to ends of the pipe.       
 */
void Pipe(int pipefd[2]);

#endif